<?php

namespace Tests;

use Fize\Http\Request;
use Fize\Http\RequestException;
use PHPUnit\Framework\TestCase;

class TestRequestException extends TestCase
{

    public function test__construct()
    {
        try {
            $request = new Request('GET', 'https://www.baidu.com/');
            throw new RequestException($request);
        } catch (RequestException $exception) {
            var_dump($exception);
            self::assertInstanceOf(RequestException::class, $exception);
        }
    }

    public function testGetRequest()
    {
        try {
            $request = new Request('GET', 'https://www.baidu.com/');
            throw new RequestException($request);
        } catch (RequestException $exception) {
            $request = $exception->getRequest();
            var_dump($request);
            self::assertEquals('www.baidu.com', $request->getUri()->getHost());
        }
    }
}
