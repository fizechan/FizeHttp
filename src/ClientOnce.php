<?php

namespace Fize\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * HTTP单次请求客户端
 */
class ClientOnce
{

    /**
     * GET 请求
     * @param string $url     指定链接
     * @param array  $headers 附加的文件头
     * @return ResponseInterface 返回响应对象
     */
    public static function get(string $url, array $headers = []): ResponseInterface
    {
        return self::sendRequest('GET', $url, null, $headers);
    }

    /**
     * POST 请求
     * @param string       $url     指定链接
     * @param string|array $body    请求体
     * @param array        $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public static function post(string $url, $body, array $headers = []): ResponseInterface
    {
        return self::sendRequest('POST', $url, $body, $headers);
    }

    /**
     * OPTIONS 请求
     * @param string $url     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public static function options(string $url, array $headers = []): ResponseInterface
    {
        return self::sendRequest('OPTIONS', $url, null, $headers);
    }

    /**
     * HEAD 请求
     * @param string $url     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public static function head(string $url, array $headers = []): ResponseInterface
    {
        $opts = [
            CURLOPT_NOBODY => true  // 不返回主体内容，否则会超时。
        ];
        return self::sendRequest('HEAD', $url, null, $headers, $opts);
    }

    /**
     * DELETE 请求
     * @param string $url     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public static function delete(string $url, array $headers = []): ResponseInterface
    {
        return self::sendRequest('DELETE', $url, null, $headers);
    }

    /**
     * PATCH 请求
     * @param string $url     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public static function patch(string $url, array $headers = []): ResponseInterface
    {
        return self::sendRequest('PATCH', $url, null, $headers);
    }

    /**
     * PUT 请求
     * @param string       $url     指定链接
     * @param string|array $body    请求体
     * @param array        $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public static function put(string $url, $body = '', array $headers = []): ResponseInterface
    {
        return self::sendRequest('PUT', $url, $body, $headers);
    }

    /**
     * TRACE 请求
     * @param string $url     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public static function trace(string $url, array $headers = []): ResponseInterface
    {
        return self::sendRequest('TRACE', $url, null, $headers);
    }

    /**
     * MOVE 请求
     * @param string $url     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public static function move(string $url, array $headers = []): ResponseInterface
    {
        return self::sendRequest('MOVE', $url, null, $headers);
    }

    /**
     * COPY 请求
     * @param string $url     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public static function copy(string $url, array $headers = []): ResponseInterface
    {
        return self::sendRequest('COPY', $url, null, $headers);
    }

    /**
     * LINK 请求
     * @param string $url     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public static function link(string $url, array $headers = []): ResponseInterface
    {
        return self::sendRequest('LINK', $url, null, $headers);
    }

    /**
     * UNLINK 请求
     * @param string $url     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public static function unlink(string $url, array $headers = []): ResponseInterface
    {
        return self::sendRequest('UNLINK', $url, null, $headers);
    }

    /**
     * WRAPPED 请求
     * @param string $url     指定链接
     * @param array  $headers 设定请求头设置
     * @return ResponseInterface 返回响应对象
     */
    public static function wrapped(string $url, array $headers = []): ResponseInterface
    {
        return self::sendRequest('WRAPPED', $url, null, $headers);
    }

    /**
     * 发送HTTP请求
     * @param string       $method     请求方式
     * @param string       $url        请求URL
     * @param string|array $body       请求体
     * @param array        $headers    报头信息
     * @param array        $opts       CURL选项
     * @param string|null  $cookie_dir 指定保存COOKIE文件的路径，默认null表示不使用COOKIE
     * @param int          $time_out   设定超时时间,默认30秒
     * @param int          $retries    curl重试次数
     * @return ResponseInterface 返回响应对象
     */
    protected static function sendRequest(string $method, string $url, $body = null, array $headers = [], array $opts = [], string $cookie_dir = null, int $time_out = 30, int $retries = 1): ResponseInterface
    {
        $uri = new Uri($url);
        $client = new ClientSimple($uri->getHost(), $cookie_dir, $time_out, $retries, $opts);
        return $client->sendRequest($method, $uri, $body, $headers);
    }
}
