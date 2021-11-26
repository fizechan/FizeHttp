<?php

namespace Fize\Http;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * HTTP 请求
 */
class Request extends Message implements RequestInterface
{

    /**
     * @var string 请求目标
     */
    protected $requestTarget;

    /**
     * @var UriInterface 请求 URI
     */
    protected $uri;

    /**
     * @var string 请求方式
     */
    protected $method;

    /**
     * 构造
     * @param string                               $method           请求方式
     * @param string|UriInterface                  $uri              请求URI
     * @param string|null|resource|StreamInterface $body             请求体
     * @param array                                $headers          报头信息
     * @param string                               $protocol_version 协议版本
     */
    public function __construct(string $method, $uri, $body = null, array $headers = [], string $protocol_version = '1.1')
    {
        $this->assertMethod($method);
        if (!($uri instanceof UriInterface)) {
            $uri = new Uri($uri);
        }

        $this->method = strtoupper($method);
        $this->uri = $uri;
        if ($headers) {
            $this->setHeaders($headers);
        }
        $this->protocolVersion = $protocol_version;

        if (!$this->hasHeader('host')) {
            $this->updateHostFromUri();
        }

        if (!is_null($body)) {
            $factory = new StreamFactory();
            if (is_string($body)) {
                $this->stream = $factory->createStream($body);
            } elseif (is_resource($body)) {
                $this->stream = $factory->createStreamFromResource($body);
            } elseif ($body instanceof StreamInterface) {
                $this->stream = $body;
            }
        }
    }

    /**
     * 获取消息的请求目标
     * @return string
     */
    public function getRequestTarget(): string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();
        if ($target == '') {
            $target = '/';
        }
        if ($this->uri->getQuery() != '') {
            $target .= '?' . $this->uri->getQuery();
        }

        return $target;
    }

    /**
     * 返回一个指定目标的请求实例
     * @param string $requestTarget 请求目标
     * @return self
     */
    public function withRequestTarget($requestTarget): Request
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new InvalidArgumentException('Invalid request target provided; cannot contain whitespace');
        }

        $new = clone $this;
        $new->requestTarget = $requestTarget;
        return $new;
    }

    /**
     * 获取当前请求使用的 HTTP 方法
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * 返回更改了请求方法的消息实例
     * @param string $method 请求方法
     * @return self
     */
    public function withMethod($method): Request
    {
        $this->assertMethod($method);
        $new = clone $this;
        $new->method = strtoupper($method);
        return $new;
    }

    /**
     * 获取 URI 实例
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * 返回修改了 URI 的消息实例
     * @param UriInterface $uri          URI 对象
     * @param bool         $preserveHost 是否保持原 HOST 信息
     * @return self
     */
    public function withUri(UriInterface $uri, $preserveHost = false): Request
    {
        $new = clone $this;
        $new->uri = $uri;
        if (!$preserveHost || !$this->hasHeader('host')) {
            $new->updateHostFromUri();
        }
        return $new;
    }

    /**
     * 检测请求方式
     * @param mixed $method 待检测变量
     */
    protected function assertMethod($method)
    {
        if (!is_string($method) || $method === '') {
            throw new InvalidArgumentException('Method must be a non-empty string.');
        }
    }

    /**
     * 根据 Uri 更新报头的 Host 信息
     */
    private function updateHostFromUri()
    {
        $host = $this->uri->getHost();
        if ($host == '') {
            return;
        }

        if (($port = $this->uri->getPort()) !== null) {
            $host .= ':' . $port;
        }

        $name = $this->getRealHeaderName('host');
        if (is_null($name)) {
            $name = 'Host';
        }

        /**
         * Host 必须是报头第一个键名
         * @see http://tools.ietf.org/html/rfc7230#section-5.4
         */
        $this->headers = [$name => [$host]] + $this->headers;
    }
}
