<?php

namespace Tests;

use Fize\Http\Stream;
use PHPUnit\Framework\TestCase;

class TestStream extends TestCase
{

    public function test()
    {
        $st = new Stream(fopen(__FILE__, 'r'));
        var_dump($st);
        self::assertInstanceOf(Stream::class, $st);
    }
}
