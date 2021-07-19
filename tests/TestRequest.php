<?php


use fize\http\Request;
use fize\http\Uri;
use PHPUnit\Framework\TestCase;

class TestRequest extends TestCase
{

    public function test__construct()
    {
        $request = new Request('GET', 'https://www.baidu.com');
        var_dump($request);
        self::assertIsObject($request);

        $body = "123456789";
        $request = new Request('GET', 'https://www.baidu.com', $body);
        var_dump($request);
        self::assertIsObject($request);

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $request = new Request('GET', 'https://www.baidu.com', null, $headers);
        var_dump($request);
        self::assertIsObject($request);
    }

    public function testGetRequestTarget()
    {
        $request = new Request('GET', 'https://www.baidu.com/test1/test2?kw=123哈哈哈');
        $target = $request->getRequestTarget();
        var_dump($target);
        self::assertEquals('/test1/test2?kw=123%E5%93%88%E5%93%88%E5%93%88', $target);
    }

    public function testWithRequestTarget()
    {
        $request = new Request('GET', 'https://www.baidu.com/test1/test2?kw=123哈哈哈');
        $request = $request->withRequestTarget('/test3/test4');
        $target = $request->getRequestTarget();
        var_dump($target);
        self::assertEquals('/test3/test4', $target);
    }

    public function testGetMethod()
    {
        $request = new Request('GET', 'https://www.baidu.com/test1/test2?kw=123哈哈哈');
        $method = $request->getMethod();
        var_dump($method);
        self::assertEquals('GET', $method);
    }

    public function testWithMethod()
    {
        $request = new Request('GET', 'https://www.baidu.com/test1/test2?kw=123哈哈哈');
        $request = $request->withMethod('POST');
        $method = $request->getMethod();
        var_dump($method);
        self::assertEquals('POST', $method);
    }

    public function testGetUri()
    {
        $request = new Request('GET', 'https://www.baidu.com/test1/test2?kw=123哈哈哈');
        $uri = $request->getUri();
        self::assertInstanceOf(Uri::class, $uri);
    }

    public function testWithUri()
    {
        $request = new Request('GET', 'https://www.baidu.com/test1/test2?kw=123哈哈哈');
        $request = $request->withUri(new Uri('https://www.qq.com'));
        $uri = $request->getUri();
        self::assertEquals('https://www.qq.com', (string)$uri);
        $request = $request->withUri(new Uri('https://www.baidu.com'), true);
        $uri = $request->getUri();
        self::assertEquals('https://www.baidu.com', (string)$uri);
    }
}
