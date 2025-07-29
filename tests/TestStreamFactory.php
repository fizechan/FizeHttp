<?php

namespace Tests;

use Fize\Http\StreamFactory;
use PHPUnit\Framework\TestCase;

class TestStreamFactory extends TestCase
{
    public function test()
    {
        $factory = new StreamFactory();
        $st = $factory->createStreamFromFile(__FILE__);
        var_dump($st);
        self::assertNotNull($st);
    }
}
