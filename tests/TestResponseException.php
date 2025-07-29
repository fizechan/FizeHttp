<?php

namespace Tests;

use Fize\Http\Response;
use Fize\Http\ResponseException;
use PHPUnit\Framework\TestCase;

class TestResponseException extends TestCase
{

    public function test__construct()
    {
        try {
            $response = new Response('IS OK!');
            throw new ResponseException($response);
        } catch (ResponseException $exception) {
            var_dump($exception);
            self::assertNotNull($exception);
        }
    }

    public function testGetResponse()
    {
        try {
            $response = new Response('IS OK!', 400);
            throw new ResponseException($response);
        } catch (ResponseException $exception) {
            $response = $exception->getResponse();
            var_dump($response);
            self::assertEquals(400, $response->getStatusCode());
        }
    }
}
