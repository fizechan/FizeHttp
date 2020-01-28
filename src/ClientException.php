<?php


namespace fize\http;

use RuntimeException;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * 客户端异常
 */
class ClientException extends RuntimeException implements ClientExceptionInterface
{

}
