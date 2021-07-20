<?php

namespace fize\http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

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
    private $parsedBody = null;

    /**
     * @var array 请求派生的属性
     */
    private $attributes = [];

    /**
     * 构造
     * @param string                               $method           请求方式
     * @param string|UriInterface                  $uri              请求URI
     * @param string|null|resource|StreamInterface $body             请求体
     * @param array                                $headers          报头信息
     * @param array                                $serverParams     服务器参数，如 $_SERVER
     * @param string                               $protocol_version 协议版本
     */
    public function __construct(string $method, $uri, $body = null, array $headers = [], array $serverParams = [], string $protocol_version = '1.1')
    {
        $this->serverParams = $serverParams;
        parent::__construct($method, $uri, $body, $headers, $protocol_version);
    }

    /**
     * 返回服务器参数
     * @return array
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * 获取 Cookie 数据
     * @return array
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * 返回具体指定 Cookie 的实例
     * @param array $cookies Cookie数据
     * @return static
     */
    public function withCookieParams(array $cookies): ServerRequest
    {
        $new = clone $this;
        $new->cookieParams = $cookies;
        return $new;
    }

    /**
     * 获取查询字符串参数
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * 返回具体指定查询字符串参数的实例
     * @param array $query 查询字符串参数数组
     * @return static
     */
    public function withQueryParams(array $query): ServerRequest
    {
        $new = clone $this;
        $new->queryParams = $query;
        return $new;
    }

    /**
     * 获取规范化的上传文件数据
     * @return array
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * 返回使用指定的上传文件数据的新实例
     * @param array $uploadedFiles 指定的上传文件数据
     * @return static
     */
    public function withUploadedFiles(array $uploadedFiles): ServerRequest
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
    public function withParsedBody($data): ServerRequest
    {
        $new = clone $this;
        $new->parsedBody = $data;
        return $new;
    }

    /**
     * 获取从请求派生的属性
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * 获取单个派生的请求属性
     * @param string $name    键名
     * @param mixed  $default 默认值
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
     * @param string $name  键名
     * @param mixed  $value 键值
     * @return static
     */
    public function withAttribute($name, $value): ServerRequest
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
    public function withoutAttribute($name): ServerRequest
    {
        if (false === array_key_exists($name, $this->attributes)) {
            return $this;
        }

        $new = clone $this;
        unset($new->attributes[$name]);
        return $new;
    }
}
