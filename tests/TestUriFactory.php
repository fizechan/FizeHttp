<?php


use fize\http\UriFactory;
use fize\http\Uri;
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
