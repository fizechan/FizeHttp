<?php

namespace fize\http;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * HTTP 请求工厂类
 */
class RequestFactory implements RequestFactoryInterface
{

    /**
     * 创建一个新的请求
     * @param string              $method 请求使用的 HTTP 方法
     * @param UriInterface|string $uri    请求关联的 URI
     * @return RequestInterface
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        return new Request($method, $uri);
    }
}
