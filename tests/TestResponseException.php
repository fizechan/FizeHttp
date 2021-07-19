<?php


use fize\http\Response;
use fize\http\ResponseException;
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
            self::assertInstanceOf(ResponseException::class, $exception);
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
