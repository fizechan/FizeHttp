<?php

namespace Fize\Http;

use Fize\IO\File;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * 并发 Http 客户端
 */
class ClientMulti
{

    /**
     * @var RequestInterface[] 请求数组
     */
    protected $requests = [];

    /**
     * @var string|null 保存COOKIE文件的文件夹路径，为null时表示不使用COOKIE
     */
    protected $cookieFileDir;

    /**
     * @var int 设定超时时间，单位秒
     */
    protected $timeOut;

    /**
     * @var int CURL重试次数
     */
    protected $retries;

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
    }

    /**
     * 添加一个请求
     * @param RequestInterface $request
     * @param int|string       $key
     * @return int|string
     */
    public function addRequest(RequestInterface $request, $key = null)
    {
        if (is_null($key)) {
            $this->requests[] = $request;
            return count($this->requests) - 1;
        }
        $this->requests[$key] = $request;
        return $key;
    }

    /**
     * 并发发送全部请求，返回结果数组
     *
     * 返回数组的键名与 addRequest / sendRequests 传入的键名（或自动索引）保持一致。
     * 发送完成后会清空当前请求队列，以避免重复发送。
     * @param array $exceptions 收集到的异常对象
     * @return ResponseInterface[] 失败则元素为null。
     */
    public function send(array &$exceptions = []): array
    {
        if (empty($this->requests)) {
            return [];
        }

        $cm = new CurlMulti();
        $handles = [];   // key => Curl 对象
        foreach ($this->requests as $key => $request) {
            $curl = $this->buildHandle($request);
            $handles[$key] = $curl;
            $cm->addHandle($curl->getHandle());
        }

        // 并发执行所有句柄
        $still_running = 0;
        do {
            $mrc = $cm->exec($still_running);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($still_running && $mrc == CURLM_OK) {
            if ($cm->select() != -1) {
                do {
                    $mrc = $cm->exec($still_running);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        $responses = [];
        foreach ($handles as $key => $curl) {
            $ch = $curl->getHandle();
            $content = CurlMulti::getcontent($ch);
            $info = $curl->getinfo();

            if ($curl->errno()) {
                if ($curl->errno() == CURLE_COULDNT_RESOLVE_HOST || $curl->errno() == CURLE_COULDNT_CONNECT) {
                    $exceptions[$key] = new NetworkException($this->requests[$key], $curl->error(), $curl->errno());
                    $responses[$key] = null;
                    continue;
                }
                $exceptions[$key] = new ClientException($curl->error(), $curl->errno());
                $responses[$key] = null;
                continue;
            }

            $header_size = $curl->getinfo(CURLINFO_HEADER_SIZE);
            $headers = $this->analysisHeaders(substr($content, 0, $header_size));
            $body = substr($content, $header_size);

            $responses[$key] = new Response($body, intval($info['http_code']), $headers);
        }

        // 清空请求队列，避免重复发送
        $this->requests = [];

        return $responses;
    }

    /**
     * 发送 PSR-7 标准的请求数组，返回一个 PSR-7 标准的响应数组
     * @param RequestInterface[] $requests   请求数组
     * @param array              $exceptions 收集到的异常对象
     * @return ResponseInterface[] 失败则元素为null。
     */
    public function sendRequests(array $requests, array &$exceptions = []): array
    {
        $this->requests = $requests;
        return $this->send($exceptions);
    }

    /**
     * 根据 PSR-7 请求构建一个已配置好的 CURL 句柄
     * @param RequestInterface $request 请求对象
     * @return Curl
     * @throws RequestException
     */
    protected function buildHandle(RequestInterface $request): Curl
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

        $options = [
            CURLOPT_HTTP_VERSION      => $http_version,
            CURLOPT_CUSTOMREQUEST     => $request->getMethod(),
            CURLOPT_HTTPHEADER        => $this->getCurlHeaders($request),
            CURLOPT_TIMEOUT           => $this->timeOut,
            CURLOPT_TIMEOUT_MS        => $this->timeOut * 1000,
            CURLOPT_CONNECTTIMEOUT    => $this->timeOut,
            CURLOPT_CONNECTTIMEOUT_MS => $this->timeOut * 1000,
            CURLOPT_HEADER            => true,  // 返回响应头
            CURLOPT_RETURNTRANSFER    => true,  // 指定返回结果而不直接输出
        ];

        if (!is_null($this->cookieFileDir)) {  // COOKIE全程跟踪
            $cookie_file = $this->cookieFileDir . $request->getUri()->getHost() . '.cookie';
            new File($cookie_file, 'w'); // 自动创建文件

            $options[CURLOPT_COOKIEJAR] = $cookie_file;  // 调用后保存cookie的文件
            $options[CURLOPT_COOKIEFILE] = $cookie_file; // 要一起传送的cookie的文件
        }

        $body = $request->getBody()->getContents();
        if ($body) {
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        return new Curl($url, $options);
    }

    /**
     * 根据请求对象返回CURL使用的报头
     * @param RequestInterface $request 请求对象
     * @return array
     */
    protected function getCurlHeaders(RequestInterface $request): array
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
    protected function analysisHeaders(string $headers): array
    {
        $arr_out = [];
        $headers = explode("\r\n", $headers);
        foreach ($headers as $header) {
            $items = explode(': ', $header, 2);
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
