<?php


namespace fize\http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 请求中间件
 */
class Middleware implements MiddlewareInterface
{

    /**
     * 参与处理服务器的请求与响应
     *
     * 本例只是个范式，无业务逻辑
     * @param ServerRequestInterface $request 请求
     * @param RequestHandlerInterface $handler 处理器
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //可以在此进行其他业务逻辑处理
        return $handler->handle($request);
    }
}
