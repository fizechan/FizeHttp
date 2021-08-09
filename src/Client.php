<?php

namespace fize\http;

use fize\io\File;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Http 客户端
 */
class Client implements ClientInterface
{

    /**
     * @var string 保存COOKIE的文件夹路径,为null时表示不使用COOKIE
     */
    private $cookieFileDir;

    /**
     * @var int 设定超时时间，单位秒
     */
    private $timeOut;

    /**
     * @var int CURL重试次数
     */
    private $retries;

    /**
     * @var Curl Curl对象
     */
    private $curl;

    /**
     * @var array Curl选项
     */
    private $options = [];

    /**
     * 初始化
     * @param string|null $cookie_dir 指定保存COOKIE文件的路径，默认null表示不使用COOKIE
     * @param int         $time_out   设定超时时间,默认30秒
     * @param int         $retries    curl重试次数
     */
    public function __construct(string $cookie_dir = null, int $time_out = 30, int $retries = 1)
    {
        $this->cookieFileDir = $cookie_dir;
        $this->timeOut = $time_out;
        $this->retries = $retries;
        $this->reset();
    }

    /**
     * 析构
     */
    public function __destruct()
    {
        $this->curl->close();
    }

    /**
     * 发送一个 PSR-7 标准的请求，返回一个 PSR-7 标准的响应
     * @param RequestInterface $request 请求
     * @return ResponseInterface
     * @throws ClientException
     * @throws NetworkException
     * @throws RequestException
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $url = (string)$request->getUri();
        if (empty($url)) {
            throw new RequestException($request);
        }
        if (empty($request->getMethod())) {
            throw new RequestException($request);
        }

        $http_version = CURL_HTTP_VERSION_NONE;
        $protocol_version = $request->getProtocolVersion();
        if ($protocol_version == '1.0') {
            $http_version = CURL_HTTP_VERSION_1_0;
        } elseif ($protocol_version == '1.1') {
            $http_version = CURL_HTTP_VERSION_1_1;
        } elseif ($protocol_version == '2.0') {
            $http_version = CURL_HTTP_VERSION_2_0;
        }
        $this->setOption(CURLOPT_HTTP_VERSION, $http_version);

        $this->setOption(CURLOPT_URL, $url);
        $this->setOption(CURLOPT_CUSTOMREQUEST, $request->getMethod());
        $this->setOption(CURLOPT_HTTPHEADER, $this->getCurlHeaders($request));

        if (!is_null($this->cookieFileDir)) {  // COOKIE全程跟踪
            $cookie_file = $this->cookieFileDir . "{$request->getUri()->getHost()}.cookie";
            new File($cookie_file, 'w'); // 自动创建文件

            $pls_opts = [
                CURLOPT_COOKIEJAR  => $cookie_file, // 调用后保存cookie的文件
                CURLOPT_COOKIEFILE => $cookie_file, // 要一起传送的cookie的文件
            ];
            $this->setOptions($pls_opts);
        }

        $body = $request->getBody()->getContents();
        if ($body) {
            $this->setOption(CURLOPT_POSTFIELDS, $body);
        }

        $content = $this->curl->exec();
        $status = $this->curl->getinfo();

        $not_ok_http_codes = ['0'];
        while (in_array($status["http_code"], $not_ok_http_codes) && (--$this->retries > 0)) {
            $content = $this->curl->exec();
            $status = $this->curl->getinfo();
        }

        $headerSize = $this->curl->getinfo(CURLINFO_HEADER_SIZE);
        $headers = substr($content, 0, $headerSize);
        $headers = $this->analysisHeaders($headers);
        $body = substr($content, $headerSize);

        if ($this->curl->errno()) {
            if ($this->curl->errno() == CURLE_COULDNT_RESOLVE_HOST || $this->curl->errno() == CURLE_COULDNT_CONNECT) {
                throw new NetworkException($request, $this->curl->error(), $this->curl->errno());
            } else {
                throw new ClientException($this->curl->error(), $this->curl->errno());
            }
        }

        if (
            isset($this->options[CURLOPT_FOLLOWLOCATION]) && $this->options[CURLOPT_FOLLOWLOCATION] &&
            isset($headers['Location']) && !empty($headers['Location'])
        ) {
            if ($headers['Location'] == $url) {
                return new Response($body, intval($status["http_code"]), $headers);
            }

            $request = $request->withUri(new Uri($headers['Location']));
            return $this->sendRequest($request);
        }

        return new Response($body, intval($status["http_code"]), $headers);
    }

    /**
     * 重置CURL选项
     */
    private function reset()
    {
        unset($this->curl);
        $this->curl = new Curl();

        // 默认配置
        $def_opts = [
            CURLOPT_TIMEOUT           => $this->timeOut,
            CURLOPT_TIMEOUT_MS        => $this->timeOut * 1000,
            CURLOPT_CONNECTTIMEOUT    => $this->timeOut,
            CURLOPT_CONNECTTIMEOUT_MS => $this->timeOut * 1000,
            CURLOPT_HEADER            => true, // 返回响应头
            CURLOPT_RETURNTRANSFER    => true, // 指定返回结果而不直接输出
        ];

        $this->setOptions($def_opts);
        $this->options = $def_opts;
    }

    /**
     * 设置CURL选项
     * @param int   $key   键名
     * @param mixed $value 键值
     */
    public function setOption(int $key, $value)
    {
        $this->curl->setopt($key, $value);
        $this->options[$key] = $value;
    }

    /**
     * 批量添加CURL选项
     * @param array $options CURL选项
     */
    public function setOptions(array $options)
    {
        $this->curl->setoptArray($options);
        $this->options = $this->options + $options; // 本处由于是数字键名，所以不能使用array_merge
    }

    /**
     * 根据请求对象返回CURL使用的报头
     * @param RequestInterface $request 请求对象
     * @return array
     */
    private function getCurlHeaders(RequestInterface $request): array
    {
        $headers = $request->getHeaders();
        $curl_headers = [];
        foreach (array_keys($headers) as $key) {
            $curl_headers[] = "$key: {$request->getHeaderLine($key)}";
        }
        return $curl_headers;
    }

    /**
     * 解析响应头成数组
     * @param string $headers 响应头字符串
     * @return array
     */
    private function analysisHeaders(string $headers): array
    {
//        return iconv_mime_decode_headers($headers);

        $arr_out = [];
        $headers = explode("\r\n", $headers);
        foreach ($headers as $header) {
            $items = explode(": ", $header, 2);
            if (count($items) != 2) {
                continue;
            }
            $key = $items[0];
            $val = trim($items[1]);
            if (preg_match('/^[\'\"]([\w\W]+)[\'\"]$/', $val, $matches)) {
                $val = $matches[1];
            }
            $arr_out[$key] = $val;
        }
        return $arr_out;
    }
}
