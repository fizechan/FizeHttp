<?php

namespace Fize\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * HTTP易用客户端
 */
class ClientEasy
{

    /**
     * @var ClientSimple HTTP简易客户端
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
        $this->client = new ClientSimple($host, $cookie_dir, $time_out, $retries, $opts);
    }

    /**
     * GET 请求
     * @param string $uri     指定链接
     * @param array  $headers 附加的文件头
     * @return ResponseInterface 返回响应对象
     */
    public function get(string $uri, array $headers = []): ResponseInterface
    {
        return $this->client->sendRequest('GET', $uri, null, $headers);
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
        return $this->client->sendRequest('POST', $uri, $body, $headers);
    }

    /**
     * OPTIONS 请求
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function options(string $uri, array $headers = []): ResponseInterface
    {
        return $this->client->sendRequest('OPTIONS', $uri, null, $headers);
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
        return $this->client->sendRequest('HEAD', $uri, null, $headers);
    }

    /**
     * DELETE 请求
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function delete(string $uri, array $headers = []): ResponseInterface
    {
        return $this->client->sendRequest('DELETE', $uri, null, $headers);
    }

    /**
     * PATCH 请求
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function patch(string $uri, array $headers = []): ResponseInterface
    {
        return $this->client->sendRequest('PATCH', $uri, null, $headers);
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
        return $this->client->sendRequest('PUT', $uri, $body, $headers);
    }

    /**
     * TRACE 请求
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function trace(string $uri, array $headers = []): ResponseInterface
    {
        return $this->client->sendRequest('TRACE', $uri, null, $headers);
    }

    /**
     * MOVE 请求
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function move(string $uri, array $headers = []): ResponseInterface
    {
        return $this->client->sendRequest('MOVE', $uri, null, $headers);
    }

    /**
     * COPY 请求
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function copy(string $uri, array $headers = []): ResponseInterface
    {
        return $this->client->sendRequest('COPY', $uri, null, $headers);
    }

    /**
     * LINK 请求
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function link(string $uri, array $headers = []): ResponseInterface
    {
        return $this->client->sendRequest('LINK', $uri, null, $headers);
    }

    /**
     * UNLINK 请求
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function unlink(string $uri, array $headers = []): ResponseInterface
    {
        return $this->client->sendRequest('UNLINK', $uri, null, $headers);
    }

    /**
     * WRAPPED 请求
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public function wrapped(string $uri, array $headers = []): ResponseInterface
    {
        return $this->client->sendRequest('WRAPPED', $uri, null, $headers);
    }
}
