<?php


namespace fize\http;

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
     * @param string $method 与请求关联的 HTTP 方法
     * @param UriInterface|string $uri 与请求关联的 URI
     * @param array $serverParams 用来生成请求实例的 SAPI 参数
     * @return ServerRequestInterface
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new ServerRequest($method, $uri, null, [], $serverParams);
    }
}
