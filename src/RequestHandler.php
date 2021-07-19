<?php

namespace fize\http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 请求处理器
 */
class RequestHandler implements RequestHandlerInterface
{

    /**
     * 处理服务器请求并返回响应
     *
     * 本例只是个范式，无实际业务逻辑
     * @param ServerRequestInterface $request 服务端请求
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // 业务逻辑处理
        return new Response();
    }
}
