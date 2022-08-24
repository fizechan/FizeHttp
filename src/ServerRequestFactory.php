<?php

namespace Fize\Http;

use Fize\Stream\Protocol\CachingStream;
use Fize\Stream\Protocol\LazyOpenStream;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * HTTP 服务端请求工厂类
 */
class ServerRequestFactory implements ServerRequestFactoryInterface
{

    /**
     * 创建一个服务端请求
     * @param string              $method       与请求关联的 HTTP 方法
     * @param UriInterface|string $uri          与请求关联的 URI
     * @param array               $serverParams 用来生成请求实例的 SAPI 参数
     * @return ServerRequestInterface
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new ServerRequest($method, $uri, null, [], $serverParams);
    }

    /**
     * 从全局变量创建ServerRequest对象
     * @return ServerRequestInterface
     */
    public function createServerRequestFromGlobals(): ServerRequestInterface
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $uri = self::getUriFromGlobals();
        $body = new CachingStream(new LazyOpenStream('php://input', 'r+'));
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $_SERVER['SERVER_PROTOCOL']) : '1.1';

        $server_request = new ServerRequest($method, $uri, $body, $headers, $_SERVER, $protocol);
        return $server_request
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withParsedBody($_POST)
            ->withUploadedFiles(self::normalizeFiles($_FILES));
    }

    /**
     * 设置服务端请求全局变量
     *
     * 本方法主要应用在HTTP的单元测试中。
     * @param ServerRequestInterface $request 服务端请求
     */
    public function setGlobals(ServerRequestInterface $request)
    {
        global $_SERVER, $_COOKIE, $_GET, $_POST, $_FILES;
        $_SERVER = $request->getServerParams();
        $_SERVER['REQUEST_METHOD'] = $request->getMethod();
        $_SERVER['SERVER_PROTOCOL'] = $request->getProtocolVersion();
        $_COOKIE = $request->getCookieParams();
        $_GET = $request->getQueryParams();
        $_POST = $request->getParsedBody();
        $_FILES = $request->getUploadedFiles();
    }

    /**
     * 从全局变量创建URI
     * @return Uri
     */
    protected static function getUriFromGlobals(): Uri
    {
        $uri = new Uri('');

        $uri = $uri->withScheme(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http');

        $has_port = false;
        if (isset($_SERVER['HTTP_HOST'])) {
            [$host, $port] = self::extractHostAndPort($_SERVER['HTTP_HOST']);
            if ($host !== null) {
                $uri = $uri->withHost($host);
            }

            if ($port !== null) {
                $has_port = true;
                $uri = $uri->withPort($port);
            }
        } elseif (isset($_SERVER['SERVER_NAME'])) {
            $uri = $uri->withHost($_SERVER['SERVER_NAME']);
        } elseif (isset($_SERVER['SERVER_ADDR'])) {
            $uri = $uri->withHost($_SERVER['SERVER_ADDR']);
        }

        if (!$has_port && isset($_SERVER['SERVER_PORT'])) {
            $uri = $uri->withPort($_SERVER['SERVER_PORT']);
        }

        $has_query = false;
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
            $uri = $uri->withPath($uri_parts[0]);
            if (isset($uri_parts[1])) {
                $has_query = true;
                $uri = $uri->withQuery($uri_parts[1]);
            }
        }

        if (!$has_query && isset($_SERVER['QUERY_STRING'])) {
            $uri = $uri->withQuery($_SERVER['QUERY_STRING']);
        }

        return $uri;
    }

    /**
     * 尝试从URI字符串中解析出主机、端口
     * @param string $authority 不严格的URI字符串
     * @return array [主机, 端口]
     */
    private static function extractHostAndPort(string $authority): array
    {
        $uri = 'https://' . $authority;
        $parts = parse_url($uri);
        if (false === $parts) {
            return [null, null];
        }

        $host = $parts['host'] ?? null;
        $port = $parts['port'] ?? null;

        return [$host, $port];
    }

    /**
     * 根据形如 $_FILES 的数组创建一个UploadedFile数组树
     * @param array $files 上传文件数组
     * @return array
     */
    protected static function normalizeFiles(array $files): array
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
            } elseif (is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = self::createUploadedFileFromSpec($value);
            } elseif (is_array($value)) {
                $normalized[$key] = self::normalizeFiles($value);
//                continue;
            } else {
                throw new InvalidArgumentException('Invalid value in files specification');
            }
        }

        return $normalized;
    }

    /**
     * 从临时文件创建UploadedFile
     * @param array $value 上传文件数组
     * @return array|UploadedFile
     */
    private static function createUploadedFileFromSpec(array $value)
    {
        if (is_array($value['tmp_name'])) {
            return self::normalizeNestedFileSpec($value);
        }

        return new UploadedFile(
            $value['tmp_name'],
            (int)$value['size'],
            (int)$value['error'],
            $value['name'],
            $value['type']
        );
    }

    /**
     * 从多个临时文件创建UploadedFile数组树
     * @param array $files
     * @return UploadedFile[]
     */
    private static function normalizeNestedFileSpec(array $files = []): array
    {
        $normalized_files = [];

        foreach (array_keys($files['tmp_name']) as $key) {
            $spec = [
                'tmp_name' => $files['tmp_name'][$key],
                'size'     => $files['size'][$key],
                'error'    => $files['error'][$key],
                'name'     => $files['name'][$key],
                'type'     => $files['type'][$key],
            ];
            $normalized_files[$key] = self::createUploadedFileFromSpec($spec);
        }

        return $normalized_files;
    }
}
