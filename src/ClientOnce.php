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
     * @param string      $url        指定链接
     * @param array       $headers    附加的文件头
     * @param array       $opts       CURL选项
     * @param string|null $cookie_dir 指定保存COOKIE文件的路径，默认null表示不使用COOKIE
     * @param int         $time_out   设定超时时间,默认30秒
     * @param int         $retries    curl重试次数
     * @return ResponseInterface 返回响应对象
     */
    public static function get(string $url, array $headers = [], array $opts = [], string $cookie_dir = null, int $time_out = 30, int $retries = 1): ResponseInterface
    {
        return self::sendRequest('GET', $url, null, $headers, $opts, $cookie_dir, $time_out, $retries);
    }

    /**
     * POST 请求
     * @param string       $url        指定链接
     * @param string|array $body       请求体
     * @param array        $headers    设定请求头设置
     * @param array        $opts       CURL选项
     * @param string|null  $cookie_dir 指定保存COOKIE文件的路径，默认null表示不使用COOKIE
     * @param int          $time_out   设定超时时间,默认30秒
     * @param int          $retries    curl重试次数
     * @return ResponseInterface 返回响应对象
     */
    public static function post(string $url, $body, array $headers = [], array $opts = [], string $cookie_dir = null, int $time_out = 30, int $retries = 1): ResponseInterface
    {
        return self::sendRequest('POST', $url, $body, $headers, $opts, $cookie_dir, $time_out, $retries);
    }

    /**
     * OPTIONS 请求
     * @param string      $url        指定链接
     * @param array       $headers    设定请求头设置
     * @param array       $opts       CURL选项
     * @param string|null $cookie_dir 指定保存COOKIE文件的路径，默认null表示不使用COOKIE
     * @param int         $time_out   设定超时时间,默认30秒
     * @param int         $retries    curl重试次数
     * @return ResponseInterface 返回响应对象
     */
    public static function options(string $url, array $headers = [], array $opts = [], string $cookie_dir = null, int $time_out = 30, int $retries = 1): ResponseInterface
    {
        return self::sendRequest('OPTIONS', $url, null, $headers, $opts, $cookie_dir, $time_out, $retries);
    }

    /**
     * HEAD 请求
     * @param string      $url        指定链接
     * @param array       $headers    设定请求头设置
     * @param array       $opts       CURL选项
     * @param string|null $cookie_dir 指定保存COOKIE文件的路径，默认null表示不使用COOKIE
     * @param int         $time_out   设定超时时间,默认30秒
     * @param int         $retries    curl重试次数
     * @return ResponseInterface 返回响应对象
     */
    public static function head(string $url, array $headers = [], array $opts = [], string $cookie_dir = null, int $time_out = 30, int $retries = 1): ResponseInterface
    {
        $opts[CURLOPT_NOBODY] = true;  // 不返回主体内容，否则会超时。
        return self::sendRequest('HEAD', $url, null, $headers, $opts, $cookie_dir, $time_out, $retries);
    }

    /**
     * DELETE 请求
     * @param string      $url        指定链接
     * @param array       $headers    设定请求头设置
     * @param array       $opts       CURL选项
     * @param string|null $cookie_dir 指定保存COOKIE文件的路径，默认null表示不使用COOKIE
     * @param int         $time_out   设定超时时间,默认30秒
     * @param int         $retries    curl重试次数
     * @return ResponseInterface 返回响应对象
     */
    public static function delete(string $url, array $headers = [], array $opts = [], string $cookie_dir = null, int $time_out = 30, int $retries = 1): ResponseInterface
    {
        return self::sendRequest('DELETE', $url, null, $headers, $opts, $cookie_dir, $time_out, $retries);
    }

    /**
     * PATCH 请求
     * @param string      $url        指定链接
     * @param array       $headers    设定请求头设置
     * @param array       $opts       CURL选项
     * @param string|null $cookie_dir 指定保存COOKIE文件的路径，默认null表示不使用COOKIE
     * @param int         $time_out   设定超时时间,默认30秒
     * @param int         $retries    curl重试次数
     * @return ResponseInterface 返回响应对象
     */
    public static function patch(string $url, array $headers = [], array $opts = [], string $cookie_dir = null, int $time_out = 30, int $retries = 1): ResponseInterface
    {
        return self::sendRequest('PATCH', $url, null, $headers, $opts, $cookie_dir, $time_out, $retries);
    }

    /**
     * PUT 请求
     * @param string       $url        指定链接
     * @param string|array $body       请求体
     * @param array        $headers    设定请求头设置
     * @param array        $opts       CURL选项
     * @param string|null  $cookie_dir 指定保存COOKIE文件的路径，默认null表示不使用COOKIE
     * @param int          $time_out   设定超时时间,默认30秒
     * @param int          $retries    curl重试次数
     * @return ResponseInterface 返回响应对象
     */
    public static function put(string $url, $body = '', array $headers = [], array $opts = [], string $cookie_dir = null, int $time_out = 30, int $retries = 1): ResponseInterface
    {
        return self::sendRequest('PUT', $url, $body, $headers, $opts, $cookie_dir, $time_out, $retries);
    }

    /**
     * TRACE 请求
     * @param string      $url        指定链接
     * @param array       $headers    设定请求头设置
     * @param array       $opts       CURL选项
     * @param string|null $cookie_dir 指定保存COOKIE文件的路径，默认null表示不使用COOKIE
     * @param int         $time_out   设定超时时间,默认30秒
     * @param int         $retries    curl重试次数
     * @return ResponseInterface 返回响应对象
     */
    public static function trace(string $url, array $headers = [], array $opts = [], string $cookie_dir = null, int $time_out = 30, int $retries = 1): ResponseInterface
    {
        return self::sendRequest('TRACE', $url, null, $headers, $opts, $cookie_dir, $time_out, $retries);
    }

    /**
     * MOVE 请求
     * @param string      $url        指定链接
     * @param array       $headers    设定请求头设置
     * @param array       $opts       CURL选项
     * @param string|null $cookie_dir 指定保存COOKIE文件的路径，默认null表示不使用COOKIE
     * @param int         $time_out   设定超时时间,默认30秒
     * @param int         $retries    curl重试次数
     * @return ResponseInterface 返回响应对象
     */
    public static function move(string $url, array $headers = [], array $opts = [], string $cookie_dir = null, int $time_out = 30, int $retries = 1): ResponseInterface
    {
        return self::sendRequest('MOVE', $url, null, $headers, $opts, $cookie_dir, $time_out, $retries);
    }

    /**
     * COPY 请求
     * @param string      $url        指定链接
     * @param array       $headers    设定请求头设置
     * @param array       $opts       CURL选项
     * @param string|null $cookie_dir 指定保存COOKIE文件的路径，默认null表示不使用COOKIE
     * @param int         $time_out   设定超时时间,默认30秒
     * @param int         $retries    curl重试次数
     * @return ResponseInterface 返回响应对象
     */
    public static function copy(string $url, array $headers = [], array $opts = [], string $cookie_dir = null, int $time_out = 30, int $retries = 1): ResponseInterface
    {
        return self::sendRequest('COPY', $url, null, $headers, $opts, $cookie_dir, $time_out, $retries);
    }

    /**
     * LINK 请求
     * @param string      $url        指定链接
     * @param array       $headers    设定请求头设置
     * @param array       $opts       CURL选项
     * @param string|null $cookie_dir 指定保存COOKIE文件的路径，默认null表示不使用COOKIE
     * @param int         $time_out   设定超时时间,默认30秒
     * @param int         $retries    curl重试次数
     * @return ResponseInterface 返回响应对象
     */
    public static function link(string $url, array $headers = [], array $opts = [], string $cookie_dir = null, int $time_out = 30, int $retries = 1): ResponseInterface
    {
        return self::sendRequest('LINK', $url, null, $headers, $opts, $cookie_dir, $time_out, $retries);
    }

    /**
     * UNLINK 请求
     * @param string      $url        指定链接
     * @param array       $headers    设定请求头设置
     * @param array       $opts       CURL选项
     * @param string|null $cookie_dir 指定保存COOKIE文件的路径，默认null表示不使用COOKIE
     * @param int         $time_out   设定超时时间,默认30秒
     * @param int         $retries    curl重试次数
     * @return ResponseInterface 返回响应对象
     */
    public static function unlink(string $url, array $headers = [], array $opts = [], string $cookie_dir = null, int $time_out = 30, int $retries = 1): ResponseInterface
    {
        return self::sendRequest('UNLINK', $url, null, $headers, $opts, $cookie_dir, $time_out, $retries);
    }

    /**
     * WRAPPED 请求
     * @param string      $url        指定链接
     * @param array       $headers    设定请求头设置
     * @param array       $opts       CURL选项
     * @param string|null $cookie_dir 指定保存COOKIE文件的路径，默认null表示不使用COOKIE
     * @param int         $time_out   设定超时时间,默认30秒
     * @param int         $retries    curl重试次数
     * @return ResponseInterface 返回响应对象
     */
    public static function wrapped(string $url, array $headers = [], array $opts = [], string $cookie_dir = null, int $time_out = 30, int $retries = 1): ResponseInterface
    {
        return self::sendRequest('WRAPPED', $url, null, $headers, $opts, $cookie_dir, $time_out, $retries);
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
