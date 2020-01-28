<?php

namespace fize\http;


use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use fize\io\File;


/**
 * Http 客户端
 */
class Client implements ClientInterface
{

    /**
     * @var string 保存COOKIE的文件夹路径,为null时表示不使用COOKIE
     */
    private $cookieFileDir = null;

    /**
     * @var int 设定超时时间，单位秒
     */
    private $timeOut = 30;

    /**
     * @var int CURL重试次数
     */
    private $retries = 1;

    /**
     * @var array CURL选项
     */
    private $options = [];

    /**
     * 初始化
     * @param string $cookie_dir 指定保存COOKIE文件的路径，默认null表示不使用COOKIE
     * @param int $time_out 设定超时时间,默认30秒
     * @param integer $retries curl重试次数
     */
    public function __construct($cookie_dir = null, $time_out = 30, $retries = 1)
    {
        $this->cookieFileDir = $cookie_dir;
        $this->timeOut = $time_out;
        $this->retries = $retries;
    }

    /**
     * 发送一个 PSR-7 标准的请求，返回一个 PSR-7 格式的响应
     * @param RequestInterface $request 请求
     * @return ResponseInterface
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->reset();

        $url = (string)$request->getUri();
        if(empty($url)) {
            throw new RequestException($request);
        }
        if (empty($request->getMethod())) {
            throw new RequestException($request);
        }

        $this->addOption(CURLOPT_URL, $url);
        $this->addOption(CURLOPT_CUSTOMREQUEST, $request->getMethod());
        $this->addOption(CURLOPT_HTTPHEADER, $this->getCurlHeaders($request));
        if (!is_null($this->cookieFileDir)) {  //COOKIE全程跟踪
            $cookie_file = $this->cookieFileDir . "{$request->getUri()->getHost()}.cookie";
            new File($cookie_file, 'w'); //自动创建文件

            $pls_opts = [
                CURLOPT_COOKIEJAR  => $cookie_file, //调用后保存cookie的文件
                CURLOPT_COOKIEFILE => $cookie_file, //要一起传送的cookie的文件
            ];
            $this->addOptions($pls_opts);
        }

        $curl = new Curl();

        $curl->setoptArray($this->options);
        $content = $curl->exec();
        $status = $curl->getinfo();

        $not_ok_http_codes = ['0'];
        while (in_array($status["http_code"], $not_ok_http_codes) && (--$this->retries > 0)) {
            $content = $curl->exec();
            $status = $curl->getinfo();
        }

        $headerSize = $curl->getinfo(CURLINFO_HEADER_SIZE);
        $headers = substr($content, 0, $headerSize);
        $headers = $this->analysisHeaders($headers);
        $body = substr($content, $headerSize);

        if ($curl->errno()) {
            if ($curl->errno() == CURLE_COULDNT_RESOLVE_HOST || $curl->errno() == CURLE_COULDNT_CONNECT) {
                throw new NetworkException($request, $curl->error(), $curl->errno());
            } else {
                throw new ClientException($curl->error(), $curl->errno());
            }
        }
        $curl->close();

        if (isset($this->options[CURLOPT_FOLLOWLOCATION]) && $this->options[CURLOPT_FOLLOWLOCATION] && isset($headers['Location']) && !empty($headers['Location'])) {
            //@todo 是否需要加上跳转来源URL
            if ($headers['Location'] == $url) {
                return new Response(intval($status["http_code"]), $headers, $body);
            }

            $request = $request->withUri(new Uri($headers['Location']));
            return $this->sendRequest($request);
        }

        return new Response(intval($status["http_code"]), $headers, $body);
    }

    /**
     * 重置CURL选项
     */
    public function reset()
    {
        //默认配置
        $def_opts = [
            CURLOPT_TIMEOUT           => $this->timeOut,
            CURLOPT_TIMEOUT_MS        => $this->timeOut * 1000,
            CURLOPT_CONNECTTIMEOUT    => $this->timeOut,
            CURLOPT_CONNECTTIMEOUT_MS => $this->timeOut * 1000,
            CURLOPT_AUTOREFERER       => true, //根据 Location: 重定向时，自动设置 header 中的Referer:信息。
            CURLOPT_FILETIME          => true, //尝试获取远程文档中的修改时间信息
            CURLOPT_FOLLOWLOCATION    => true, //根据服务器返回 HTTP 头中的 "Location: " 重定向
            CURLOPT_SSL_VERIFYPEER    => false, //禁止cURL验证对等证书
            CURLOPT_SSL_VERIFYHOST    => false, //不检查服务器SSL证书中是否存在一个公用名
            CURLOPT_SSLVERSION        => 1, //使用CURL_SSLVERSION_TLSv1，在 SSLv2 和 SSLv3 中有弱点存在。
            CURLOPT_HEADER            => true, //返回响应头
            CURLOPT_RETURNTRANSFER    => true, //指定返回结果而不直接输出
        ];
        $this->options = $def_opts;
    }

    /**
     * 添加CURL选项
     * @param int $key 键名
     * @param mixed $value 键值
     */
    public function addOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    /**
     * 批量添加CURL选项
     * @param array $options CURL选项
     */
    public function addOptions(array $options)
    {
        $this->options = $this->options + $options; //本处由于是数字键名，所以不能使用array_merge
    }

    /**
     * 根据请求对象返回CURL使用的报头
     * @param RequestInterface $request 请求对象
     * @return array
     */
    private function getCurlHeaders(RequestInterface $request)
    {
        $headers = $request->getHeaders();
        $curl_headers = [];
        foreach (array_keys($headers) as $key) {
            $curl_headers[] = "{$key}: {$request->getHeaderLine($key)}";
        }
        return $curl_headers;
    }

    /**
     * 解析响应头成数组
     * @param $headers string 响应头字符串
     * @return array
     */
    private function analysisHeaders($headers)
    {
        //return iconv_mime_decode_headers($headers);

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
