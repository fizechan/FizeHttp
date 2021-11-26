<?php

namespace Tests;

use Fize\Http\Request;
use Fize\Http\RequestFactory;
use PHPUnit\Framework\TestCase;

class TestRequestFactory extends TestCase
{

    public function testCreateRequest()
    {
        $factory = new RequestFactory();
        $request = $factory->createRequest('GET', 'https://www.baidu.com');
        var_dump($request);
        self::assertInstanceOf(Request::class, $request);
    }
}
