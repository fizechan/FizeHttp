<?php

namespace Fize\Http;

use CURLFile;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * HTTP简易客户端
 */
class ClientSimple
{

    /**
     * @var string 主机名
     */
    protected $host;

    /**
     * @var Client HTTP客户端
     */
    protected $client;

    /**
     * 初始化
     * @param string      $host       主机名
     * @param string|null $cookie_dir 指定保存COOKIE文件的路径，默认null表示不使用COOKIE
     * @param int         $time_out   设定超时时间,默认30秒
     * @param int         $retries    curl重试次数
     * @param array       $opts       CURL选项
     */
    public function __construct(string $host, string $cookie_dir = null, int $time_out = 30, int $retries = 1, array $opts = [])
    {
        $this->host = $host;
        $this->client = new Client($cookie_dir, $time_out, $retries);
        if ($opts) {
            $this->client->setOptions($opts);
        }
    }

    /**
     * GET 请求
     * @param string $uri     指定链接
     * @param array  $headers 附加的文件头
     * @return ResponseInterface 返回响应对象
     */
    public function get(string $uri, array $headers = []): ResponseInterface
    {
        return $this->sendRequest('GET', $uri, null, $headers);
    }

    /**
     * POST 请求
     * @param string       $uri     指定链接
     * @param string|array $body    请求体
     * @param array        $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function post(string $uri, $body, array $headers = []): ResponseInterface
    {
        return $this->sendRequest('POST', $uri, $body, $headers);
    }

    /**
     * OPTIONS 请求
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function options(string $uri, array $headers = []): ResponseInterface
    {
        return $this->sendRequest('OPTIONS', $uri, null, $headers);
    }

    /**
     * HEAD 请求
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function head(string $uri, array $headers = []): ResponseInterface
    {
        $opts = [
            CURLOPT_NOBODY => true  // 不返回主体内容，否则会超时。
        ];
        $this->client->setOptions($opts);
        return $this->sendRequest('HEAD', $uri, null, $headers);
    }

    /**
     * DELETE 请求
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function delete(string $uri, array $headers = []): ResponseInterface
    {
        return $this->sendRequest('DELETE', $uri, null, $headers);
    }

    /**
     * PATCH 请求
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function patch(string $uri, array $headers = []): ResponseInterface
    {
        return $this->sendRequest('PATCH', $uri, null, $headers);
    }

    /**
     * PUT 请求
     * @param string       $uri     指定链接
     * @param string|array $body    请求体
     * @param array        $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function put(string $uri, $body = '', array $headers = []): ResponseInterface
    {
        return $this->sendRequest('PUT', $uri, $body, $headers);
    }

    /**
     * TRACE 请求
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function trace(string $uri, array $headers = []): ResponseInterface
    {
        return $this->sendRequest('TRACE', $uri, null, $headers);
    }

    /**
     * MOVE 请求
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function move(string $uri, array $headers = []): ResponseInterface
    {
        return $this->sendRequest('MOVE', $uri, null, $headers);
    }

    /**
     * COPY 请求
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function copy(string $uri, array $headers = []): ResponseInterface
    {
        return $this->sendRequest('COPY', $uri, null, $headers);
    }

    /**
     * LINK 请求
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function link(string $uri, array $headers = []): ResponseInterface
    {
        return $this->sendRequest('LINK', $uri, null, $headers);
    }

    /**
     * UNLINK 请求
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function unlink(string $uri, array $headers = []): ResponseInterface
    {
        return $this->sendRequest('UNLINK', $uri, null, $headers);
    }

    /**
     * WRAPPED 请求
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function wrapped(string $uri, array $headers = []): ResponseInterface
    {
        return $this->sendRequest('WRAPPED', $uri, null, $headers);
    }

    /**
     * 发送HTTP请求
     * @param string              $method  请求方式
     * @param string|UriInterface $uri     请求URI
     * @param string|array        $body    请求体
     * @param array               $headers 报头信息
     * @return ResponseInterface 返回响应对象
     */
    protected function sendRequest(string $method, $uri, $body = null, array $headers = []): ResponseInterface
    {
        $url = $this->host . $uri;
        $data = null;
        if (is_string($body)) {
            $data = $body;
        } elseif (self::isUploadFile($body)) {
            $data = $body;  // 需要POST上传文件时直接传递数组
        } elseif (!empty($body)) {
            $data = http_build_query($body);
        }
        if (!is_null($data)) {
            $this->client->setOption(CURLOPT_POSTFIELDS, $data);
        }

        if (is_array($body)) {
            $body = null;  // 使用CURL直接传递body
        }

        $request = new Request($method, $url, $body, $headers);
        return $this->client->sendRequest($request);
    }

    /**
     * 判断上传的东西是否包含文件上传
     * @param mixed $body 请求体
     * @return bool
     */
    private static function isUploadFile($body): bool
    {
        if (!is_array($body)) {
            return false;
        }
        foreach ($body as $val) {
            if ($val instanceof CURLFile) {
                return true;
            }
        }
        return false;
    }
}
