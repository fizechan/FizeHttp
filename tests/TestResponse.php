<?php

namespace Tests;

use Fize\Http\Response;
use PHPUnit\Framework\TestCase;

class TestResponse extends TestCase
{

    public function test__construct()
    {
        $response = new Response('这是响应内容');
        var_dump($response);
        self::assertNotNull($response);

        $response = new Response('这是响应内容', 400);
        var_dump($response);
        self::assertNotNull($response);

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $response = new Response('这是响应内容', 200, $headers);
        var_dump($response);
        self::assertNotNull($response);
    }

    public function testGetStatusCode()
    {
        $response = new Response('这是响应内容', 400);
        $scode = $response->getStatusCode();
        self::assertEquals(400, $scode);
    }

    public function testWithStatus()
    {
        $response = new Response('这是响应内容');
        $response = $response->withStatus(500);
        $scode = $response->getStatusCode();
        self::assertEquals(500, $scode);
    }

    public function testGetReasonPhrase()
    {
        $response = new Response('这是响应内容', 502);
        $reason = $response->getReasonPhrase();
        var_dump($reason);
        self::assertNotEmpty($reason);
    }
}
