<?php

use fize\http\StreamFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class TestStreamFactory extends TestCase
{
    public function test()
    {
        $factory = new StreamFactory();
        $st = $factory->createStreamFromFile(__FILE__);
        var_dump($st);
        self::assertInstanceOf(StreamInterface::class, $st);
    }
}
