<?php


namespace fize\http;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\UploadedFileInterface;
use fize\stream\protocol\CachingStream;
use fize\stream\protocol\LazyOpenStream;

/**
 * 服务器端 HTTP 请求
 */
class ServerRequest extends Request implements ServerRequestInterface
{

    /**
     * @var array 服务器参数
     */
    private $serverParams;

    /**
     * @var array Cookie 数据
     */
    private $cookieParams = [];

    /**
     * @var array 查询字符串参数
     */
    private $queryParams = [];

    /**
     * @var array 获取规范化的上传文件数据
     */
    private $uploadedFiles = [];

    /**
     * @var null|array|object 请求消息体中的参数
     */
    private $parsedBody;

    /**
     * @var array 请求派生的属性
     */
    private $attributes = [];

    /**
     * 构造
     * @param string $method 请求方式
     * @param string|UriInterface $uri 请求URI
     * @param string|null|resource|StreamInterface $body 请求体
     * @param array $headers 报头信息
     * @param array $serverParams 服务器参数，如 $_SERVER
     * @param string $protocol_version 协议版本
     */
    public function __construct($method, $uri, $body = null, array $headers = [], array $serverParams = [], $protocol_version = '1.1')
    {
        $this->serverParams = $serverParams;
        parent::__construct($method, $uri, $body, $headers, $protocol_version);
    }

    /**
     * 返回服务器参数
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * 获取 Cookie 数据
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * 返回具体指定 Cookie 的实例
     * @param array $cookies Cookie数据
     * @return static
     */
    public function withCookieParams(array $cookies)
    {
        $new = clone $this;
        $new->cookieParams = $cookies;

        return $new;
    }

    /**
     * 获取查询字符串参数
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * 返回具体指定查询字符串参数的实例
     * @param array $query 查询字符串参数数组
     * @return static
     */
    public function withQueryParams(array $query)
    {
        $new = clone $this;
        $new->queryParams = $query;

        return $new;
    }

    /**
     * 获取规范化的上传文件数据
     * @return array
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * 返回使用指定的上传文件数据的新实例
     * @param array $uploadedFiles 指定的上传文件数据
     * @return static
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }

    /**
     * 获取请求消息体中的参数
     *
     * 如果请求的 Content-Type 是 application/x-www-form-urlencoded 或 multipart/form-data 且请求方法是 POST，则此方法 **必须** 返回 $_POST 的内容。
     * 如果是其他情况，此方法可能返回反序列化请求正文内容的任何结果
     * @return array|object|null
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * 返回具有指定消息体参数的实例
     *
     * 如果请求的 Content-Type 是 application/x-www-form-urlencoded 或 multipart/form-data 且请求方法是 POST，则方法的参数只能是 $_POST
     * 数据不一定要来自 $_POST，但是 **必须** 是反序列化请求正文内容的结果。由于需要反序列化/解析返回的结构化数据，所以这个方法只接受数组、 `object` 类型和 `null`（如果没有可用的数据解析）。
     * @param array|object|null $data 反序列化的消息体数据
     * @return static
     */
    public function withParsedBody($data)
    {
        $new = clone $this;
        $new->parsedBody = $data;

        return $new;
    }

    /**
     * 获取从请求派生的属性
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * 获取单个派生的请求属性
     * @param string $name 键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        if (false === array_key_exists($name, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$name];
    }

    /**
     * 返回具有指定派生属性的实例
     * @param string $name 键名
     * @param mixed $value 键值
     * @return static
     */
    public function withAttribute($name, $value)
    {
        $new = clone $this;
        $new->attributes[$name] = $value;

        return $new;
    }

    /**
     * 返回移除指定属性的实例
     * @param string $name 键名
     * @return static
     */
    public function withoutAttribute($name)
    {
        if (false === array_key_exists($name, $this->attributes)) {
            return $this;
        }

        $new = clone $this;
        unset($new->attributes[$name]);

        return $new;
    }

    /**
     * 根据形如 $_FILES 的数组创建一个UploadedFile数组树
     * @param array $files 上传文件数组
     * @return array
     */
    public static function normalizeFiles(array $files)
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
            } elseif (is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = self::createUploadedFileFromSpec($value);
            } elseif (is_array($value)) {
                $normalized[$key] = self::normalizeFiles($value);
                continue;
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
            (int) $value['size'],
            (int) $value['error'],
            $value['name'],
            $value['type']
        );
    }

    /**
     * 从多个临时文件创建UploadedFile数组树
     * @param array $files
     * @return UploadedFile[]
     */
    private static function normalizeNestedFileSpec(array $files = [])
    {
        $normalizedFiles = [];

        foreach (array_keys($files['tmp_name']) as $key) {
            $spec = [
                'tmp_name' => $files['tmp_name'][$key],
                'size'     => $files['size'][$key],
                'error'    => $files['error'][$key],
                'name'     => $files['name'][$key],
                'type'     => $files['type'][$key],
            ];
            $normalizedFiles[$key] = self::createUploadedFileFromSpec($spec);
        }

        return $normalizedFiles;
    }

    /**
     * 从全局变量创建ServerRequest对象
     * @return static
     * @noinspection PhpComposerExtensionStubsInspection
     */
    public static function fromGlobals()
    {
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $headers = getallheaders();
        $uri = self::getUriFromGlobals();
        $body = new CachingStream(new LazyOpenStream('php://input', 'r+'));
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $_SERVER['SERVER_PROTOCOL']) : '1.1';

        $serverRequest = new static($method, $uri, $body, $headers, $_SERVER, $protocol);

        return $serverRequest
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withParsedBody($_POST)
            ->withUploadedFiles(self::normalizeFiles($_FILES));
    }

    /**
     * 从全局变量创建URI
     * @return Uri
     */
    public static function getUriFromGlobals()
    {
        $uri = new Uri('');

        $uri = $uri->withScheme(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http');

        $hasPort = false;
        if (isset($_SERVER['HTTP_HOST'])) {
            list($host, $port) = self::extractHostAndPortFromAuthority($_SERVER['HTTP_HOST']);
            if ($host !== null) {
                $uri = $uri->withHost($host);
            }

            if ($port !== null) {
                $hasPort = true;
                $uri = $uri->withPort($port);
            }
        } elseif (isset($_SERVER['SERVER_NAME'])) {
            $uri = $uri->withHost($_SERVER['SERVER_NAME']);
        } elseif (isset($_SERVER['SERVER_ADDR'])) {
            $uri = $uri->withHost($_SERVER['SERVER_ADDR']);
        }

        if (!$hasPort && isset($_SERVER['SERVER_PORT'])) {
            $uri = $uri->withPort($_SERVER['SERVER_PORT']);
        }

        $hasQuery = false;
        if (isset($_SERVER['REQUEST_URI'])) {
            $requestUriParts = explode('?', $_SERVER['REQUEST_URI'], 2);
            $uri = $uri->withPath($requestUriParts[0]);
            if (isset($requestUriParts[1])) {
                $hasQuery = true;
                $uri = $uri->withQuery($requestUriParts[1]);
            }
        }

        if (!$hasQuery && isset($_SERVER['QUERY_STRING'])) {
            $uri = $uri->withQuery($_SERVER['QUERY_STRING']);
        }

        return $uri;
    }

    /**
     * 尝试从URI字符串中解析出主机、端口
     * @param string $authority 不严格的URI字符串
     * @return array
     */
    private static function extractHostAndPortFromAuthority($authority)
    {
        $uri = 'http://'.$authority;
        $parts = parse_url($uri);
        if (false === $parts) {
            return [null, null];
        }

        $host = isset($parts['host']) ? $parts['host'] : null;
        $port = isset($parts['port']) ? $parts['port'] : null;

        return [$host, $port];
    }
}
