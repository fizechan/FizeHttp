<?php


namespace fize\http;

use Throwable;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;

/**
 * 网络异常
 */
class NetworkException extends ClientException implements NetworkExceptionInterface
{

    /**
     * @var RequestInterface 请求
     */
    private $request;

    /**
     * 构造
     * @param RequestInterface $request 请求体
     * @param string $message 错误信息
     * @param int $code 错误码
     * @param Throwable|null $previous 用于异常链接
     */
    public function __construct(RequestInterface $request, $message = "", $code = 0, Throwable $previous = null)
    {
        $this->request = $request;
        parent::__construct($message, $code, $previous);
    }

    /**
     * 获取请求对象
     *
     * 请求对象可能和客户端接口发送的对象不一致
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
