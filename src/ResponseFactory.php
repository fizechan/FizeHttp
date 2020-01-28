<?php


namespace fize\http;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * HTTP 响应工厂类
 */
class ResponseFactory implements ResponseFactoryInterface
{

    /**
     * 创建一个响应对象
     * @param int $code HTTP 状态码
     * @param string $reasonPhrase 状态码短语
     * @return ResponseInterface
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return new Response($code, [], null, '1.1', $reasonPhrase);
    }
}
