<?php

namespace Fize\Http;

use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;

/**
 * 客户端异常
 */
class ClientException extends RuntimeException implements ClientExceptionInterface
{

}
