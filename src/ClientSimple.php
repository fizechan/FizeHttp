<?php

namespace Fize\Http;

use CURLFile;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Http 简易客户端
 */
class ClientSimple
{
    /**
     * 禁止实例化
     */
    private function __construct()
    {
    }

    /**
     * GET 请求
     *
     * 参数 `$config` :
     *   ['cookie_dir' => *, 'time_out' => *, 'retries' => *]
     * @param string $uri     指定链接
     * @param array  $headers 附加的文件头
     * @param array  $opts    参数配置数组
     * @param array  $config  客户端配置
     * @return ResponseInterface 返回响应对象
     */
    public static function get(string $uri, array $headers = [], array $opts = [], array $config = []): ResponseInterface
    {
        return self::send('GET', $uri, null, $headers, $opts, $config);
    }

    /**
     * POST 请求
     *
     * 参数 `$config` :
     *   ['cookie_dir' => *, 'time_out' => *, 'retries' => *]
     * @param string       $uri     指定链接
     * @param string|array $body    请求体
     * @param array        $headers 设定请求头设置
     * @param array        $opts    参数配置数组
     * @param array        $config  客户端配置
     * @return ResponseInterface 返回响应对象
     */
    public static function post(string $uri, $body, array $headers = [], array $opts = [], array $config = []): ResponseInterface
    {
        return self::send('POST', $uri, $body, $headers, $opts, $config);
    }

    /**
     * OPTIONS 请求
     *
     * 参数 `$config` :
     *   ['cookie_dir' => *, 'time_out' => *, 'retries' => *]
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @param array  $opts    参数配置数组
     * @param array  $config  客户端配置
     * @return ResponseInterface 返回响应对象
     */
    public static function options(string $uri, array $headers = [], array $opts = [], array $config = []): ResponseInterface
    {
        return self::send('OPTIONS', $uri, null, $headers, $opts, $config);
    }

    /**
     * HEAD 请求
     *
     * 参数 `$config` :
     *   ['cookie_dir' => *, 'time_out' => *, 'retries' => *]
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @param array  $opts    参数配置数组
     * @param array  $config  客户端配置
     * @return ResponseInterface 返回响应对象
     */
    public static function head(string $uri, array $headers = [], array $opts = [], array $config = []): ResponseInterface
    {
        $def_opts = [
            CURLOPT_NOBODY => true  // 不返回主体内容，否则会超时。
        ];
        $opts = $opts + $def_opts;
        return self::send('HEAD', $uri, null, $headers, $opts, $config);
    }

    /**
     * DELETE 请求
     *
     * 参数 `$config` :
     *   ['cookie_dir' => *, 'time_out' => *, 'retries' => *]
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @param array  $opts    参数配置数组
     * @param array  $config  客户端配置
     * @return ResponseInterface 返回响应对象
     */
    public static function delete(string $uri, array $headers = [], array $opts = [], array $config = []): ResponseInterface
    {
        return self::send('DELETE', $uri, null, $headers, $opts, $config);
    }

    /**
     * PATCH 请求
     *
     * 参数 `$config` :
     *   ['cookie_dir' => *, 'time_out' => *, 'retries' => *]
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @param array  $opts    参数配置数组
     * @param array  $config  客户端配置
     * @return ResponseInterface 返回响应对象
     */
    public static function patch(string $uri, array $headers = [], array $opts = [], array $config = []): ResponseInterface
    {
        return self::send('PATCH', $uri, null, $headers, $opts, $config);
    }

    /**
     * PUT 请求
     *
     * 参数 `$config` :
     *   ['cookie_dir' => *, 'time_out' => *, 'retries' => *]
     * @param string       $uri     指定链接
     * @param string|array $body    请求体
     * @param array        $headers 设定请求头设置
     * @param array        $opts    参数配置数组
     * @param array        $config  客户端配置
     * @return ResponseInterface 返回响应对象
     */
    public static function put(string $uri, $body = '', array $headers = [], array $opts = [], array $config = []): ResponseInterface
    {
        return self::send('PUT', $uri, $body, $headers, $opts, $config);
    }

    /**
     * TRACE 请求
     *
     * 参数 `$config` :
     *   ['cookie_dir' => *, 'time_out' => *, 'retries' => *]
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @param array  $opts    参数配置数组
     * @param array  $config  客户端配置
     * @return ResponseInterface 返回响应对象
     */
    public static function trace(string $uri, array $headers = [], array $opts = [], array $config = []): ResponseInterface
    {
        return self::send('TRACE', $uri, null, $headers, $opts, $config);
    }

    /**
     * MOVE 请求
     *
     * 参数 `$config` :
     *   ['cookie_dir' => *, 'time_out' => *, 'retries' => *]
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @param array  $opts    参数配置数组
     * @param array  $config  客户端配置
     * @return ResponseInterface 返回响应对象
     */
    public static function move(string $uri, array $headers = [], array $opts = [], array $config = []): ResponseInterface
    {
        return self::send('MOVE', $uri, null, $headers, $opts, $config);
    }

    /**
     * COPY 请求
     *
     * 参数 `$config` :
     *   ['cookie_dir' => *, 'time_out' => *, 'retries' => *]
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @param array  $opts    参数配置数组
     * @param array  $config  客户端配置
     * @return ResponseInterface 返回响应对象
     */
    public static function copy(string $uri, array $headers = [], array $opts = [], array $config = []): ResponseInterface
    {
        return self::send('COPY', $uri, null, $headers, $opts, $config);
    }

    /**
     * LINK 请求
     *
     * 参数 `$config` :
     *   ['cookie_dir' => *, 'time_out' => *, 'retries' => *]
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @param array  $opts    参数配置数组
     * @param array  $config  客户端配置
     * @return ResponseInterface 返回响应对象
     */
    public static function link(string $uri, array $headers = [], array $opts = [], array $config = []): ResponseInterface
    {
        return self::send('LINK', $uri, null, $headers, $opts, $config);
    }

    /**
     * UNLINK 请求
     *
     * 参数 `$config` :
     *   ['cookie_dir' => *, 'time_out' => *, 'retries' => *]
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @param array  $opts    参数配置数组
     * @param array  $config  客户端配置
     * @return ResponseInterface 返回响应对象
     */
    public static function unlink(string $uri, array $headers = [], array $opts = [], array $config = []): ResponseInterface
    {
        return self::send('UNLINK', $uri, null, $headers, $opts, $config);
    }

    /**
     * WRAPPED 请求
     *
     * 参数 `$config` :
     *   ['cookie_dir' => *, 'time_out' => *, 'retries' => *]
     * @param string $uri     指定链接
     * @param array  $headers 设定请求头设置
     * @param array  $opts    参数配置数组
     * @param array  $config  客户端配置
     * @return ResponseInterface 返回响应对象
     */
    public static function wrapped(string $uri, array $headers = [], array $opts = [], array $config = []): ResponseInterface
    {
        return self::send('WRAPPED', $uri, null, $headers, $opts, $config);
    }

    /**
     * 简易 HTTP 客户端
     *
     * 参数 `$config` :
     *   ['cookie_dir' => *, 'time_out' => *, 'retries' => *]
     * @param string              $method  请求方式
     * @param string|UriInterface $uri     请求URI
     * @param string|array        $body    请求体
     * @param array               $headers 报头信息
     * @param array               $opts    CURL选项
     * @param array               $config  客户端配置
     * @return ResponseInterface 返回响应对象
     */
    protected static function send(string $method, $uri, $body = null, array $headers = [], array $opts = [], array $config = []): ResponseInterface
    {
        $cookie_dir = $config['cookie_dir'] ?? null;
        $time_out = $config['time_out'] ?? 30;
        $retries = $config['retries'] ?? 1;

        $client = new Client($cookie_dir, $time_out, $retries);
        if ($opts) {
            $client->setOptions($opts);
        }

        $dataPOST = null;
        if (is_string($body)) {
            $dataPOST = $body;
        } elseif (self::isUploadFile($body)) {
            $dataPOST = $body;  // 需要POST上传文件时直接传递数组
        } elseif (!empty($body)) {
            $dataPOST = http_build_query($body);
        }
        if (!is_null($dataPOST)) {
            $client->setOption(CURLOPT_POSTFIELDS, $dataPOST);
        }

        if (is_array($body)) {
            $body = null;  // 使用CURL直接传递body
        }

        $request = new Request($method, $uri, $body, $headers);
        return $client->sendRequest($request);
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
