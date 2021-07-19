<?php

use fize\http\Response;
use fize\http\ResponseFactory;
use PHPUnit\Framework\TestCase;

class TestResponseFactory extends TestCase
{

    public function testCreateResponse()
    {
        $factory = new ResponseFactory();
        $response = $factory->createResponse();
        var_dump($response);
        self::assertInstanceOf(Response::class, $response);
    }
}
