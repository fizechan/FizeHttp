<?php


use fize\http\RequestHandler;
use fize\http\Response;
use fize\http\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TestRequestHandler extends TestCase
{

    public function testHandle()
    {
        $sreq = new ServerRequest('GET', 'https://www.baidu.com/');
        $myrh = new MyRequestHandler();
        $response = $myrh->handle($sreq);
        self::assertEquals(404, $response->getStatusCode());
    }
}

class MyRequestHandler extends RequestHandler
{
    /**
     * 处理服务器请求并返回响应
     *
     * 本例只是个范式，无业务逻辑
     * @param ServerRequestInterface $request 服务端请求
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = new Response();
        return $response->withStatus(404);
    }
}