<?php


use fize\http\RequestFactory;
use fize\http\Request;
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
