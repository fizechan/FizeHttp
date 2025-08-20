<?php

namespace Fize\Http;

use Fize\Stream\Protocol\CachingStream;
use Fize\Stream\Protocol\LazyOpenStream;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
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
            ->withUploadedFiles((new UploadedFileFactory())->createUploadedFilesFromSpec($_FILES));
    }

    /**
     * 设置服务端请求全局变量
     *
     * 本方法主要应用在HTTP的单元测试中。
     * @param ServerRequestInterface $request 服务端请求
     */
    public static function setGlobals(ServerRequestInterface $request)
    {
        global $_SERVER, $_COOKIE, $_GET, $_POST, $_REQUEST;
        $_SERVER = $request->getServerParams();
        $uri = $request->getUri();
        $host = $uri->getHost();
        $path = $uri->getPath();
        $port = $uri->getPort();
        $_SERVER['SERVER_NAME'] = $host;
        $_SERVER['REQUEST_URI'] = $path;
        $_SERVER['HTTP_HOST'] = $host;
        $_SERVER['REQUEST_METHOD'] = $request->getMethod();
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/' . $request->getProtocolVersion();
        $_SERVER['REQUEST_TIME'] = time();
        $_SERVER['QUERY_STRING'] = $uri->getQuery();
        $_SERVER['HTTP_ACCEPT'] = $request->getHeaderLine('Accept');
        $_SERVER['HTTP_ACCEPT_CHARSET'] = $request->getHeaderLine('Accept-Charset');
        $_SERVER['HTTP_REFERER'] = $host;
        $_SERVER['HTTPS'] = $uri->getScheme() === 'https' ? 'on' : 'off';
        $_SERVER['REMOTE_ADDR'] = $host;
        $_SERVER['REMOTE_HOST'] = $host;
        $_SERVER['REMOTE_PORT'] = $port;
        $_SERVER['SCRIPT_FILENAME'] = __FILE__;
        $_SERVER['SERVER_PORT'] = $port;
        $_SERVER['PATH_TRANSLATED'] = $path;
        $_SERVER['SCRIPT_NAME'] = $path;
        $_SERVER['SCRIPT_URI'] = $path;
        $_COOKIE = $request->getCookieParams();
        $_GET = $request->getQueryParams();
        $_POST = $request->getParsedBody() ?: [];
        $_REQUEST = array_merge($_GET, $_POST, $_COOKIE);
        UploadedFileFactory::setGlobalsByUploadedFiles($request->getUploadedFiles());
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
}
