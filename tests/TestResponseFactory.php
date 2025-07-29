<?php

namespace Tests;

use Fize\Http\ResponseFactory;
use PHPUnit\Framework\TestCase;

class TestResponseFactory extends TestCase
{

    public function testCreateResponse()
    {
        $factory = new ResponseFactory();
        $response = $factory->createResponse();
        var_dump($response);
        self::assertNotNull($response);
    }
}
