<?php

namespace Tests;

use Fize\Http\NetworkException;
use Fize\Http\Request;
use PHPUnit\Framework\TestCase;

class TestNetworkException extends TestCase
{

    public function test__construct()
    {
        try {
            $request = new Request('GET', 'https://www.baidu.com/');
            throw new NetworkException($request);
        } catch (NetworkException $exception) {
            var_dump($exception);
            self::assertInstanceOf(NetworkException::class, $exception);
        }
    }

    public function testGetRequest()
    {
        try {
            $request = new Request('GET', 'https://www.baidu.com/');
            throw new NetworkException($request);
        } catch (NetworkException $exception) {
            $request = $exception->getRequest();
            var_dump($request);
            self::assertEquals('www.baidu.com', $request->getUri()->getHost());
        }
    }
}
