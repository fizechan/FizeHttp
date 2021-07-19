<?php


use fize\http\Middleware;
use fize\http\RequestHandler;
use fize\http\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TestMiddleware extends TestCase
{

    public function testProcess()
    {
        $sreq = new ServerRequest('GET', 'https://www.baidu.com/');
        $reqh = new RequestHandler();
        $mymi = new MyMiddleware();
        $response = $mymi->process($sreq, $reqh);
        self::assertEquals(404, $response->getStatusCode());
    }
}

class MyMiddleware extends Middleware
{
    /**
     * 参与处理服务器的请求与响应
     *
     * 本例只是个范式，无实际业务逻辑
     * @param ServerRequestInterface  $request 请求
     * @param RequestHandlerInterface $handler 处理器
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        return $response->withStatus(404);  // 模拟404拦截
    }
}