<?php

namespace Tests;

use Fize\Http\Uri;
use Fize\Http\UriFactory;
use PHPUnit\Framework\TestCase;

class TestUriFactory extends TestCase
{

    public function testCreateUri()
    {
        $factory = new UriFactory();
        $uri = $factory->createUri('https://www.baidu.com/test1/test2?kd=123#top');
        var_dump($uri);
        self::assertInstanceOf(Uri::class, $uri);
    }
}
