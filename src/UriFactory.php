<?php

namespace Fize\Http;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * URI工厂类
 */
class UriFactory implements UriFactoryInterface
{

    /**
     * 创建一个 URI
     * @param string $uri 要解析的 URI
     * @return UriInterface
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
