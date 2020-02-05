<?php


use fize\http\Request;
use fize\http\Uri;
use fize\http\Stream;
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
        $request = $request->withUri(new Uri('https://www.baidu.com'));
        $uri = $request->getUri();
        self::assertEquals('https://www.baidu.com', (string)$uri);
    }

    public function testGetProtocolVersion()
    {
        $request = new Request('GET', 'https://www.baidu.com');
        $request = $request->withProtocolVersion('2.0');
        $version = $request->getProtocolVersion();
        self::assertEquals('2.0', $version);
    }

    public function testWithProtocolVersion()
    {
        $request = new Request('GET', 'https://www.baidu.com');
        $version = $request->getProtocolVersion();
        self::assertEquals('1.1', $version);

        $request = new Request('GET', 'https://www.baidu.com', [], null, '2.0');
        $version = $request->getProtocolVersion();
        self::assertEquals('2.0', $version);
    }

    public function testGetHeaders()
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $request = new Request('GET', 'https://www.baidu.com', $headers);
        $headers = $request->getHeaders();
        var_dump($headers);
        self::assertIsArray($headers);
    }

    public function testHasHeader()
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $request = new Request('GET', 'https://www.baidu.com', $headers);
        $has1 = $request->hasHeader('content-type');
        self::assertTrue($has1);
        $has2 = $request->hasHeader('und');
        self::assertFalse($has2);
    }

    public function testGetHeader()
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $request = new Request('GET', 'https://www.baidu.com', $headers);
        $ct = $request->getHeader('content-type');
        self::assertIsArray($ct);
        $und = $request->getHeader('und');
        self::assertEmpty($und);
    }

    public function testGetHeaderLine()
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'test' => ['val1', 'val2']
        ];
        $request = new Request('GET', 'https://www.baidu.com', $headers);
        $test = $request->getHeaderLine('test');
        var_dump($test);
        self::assertIsString($test);
    }

    public function testWithHeader()
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $request = new Request('GET', 'https://www.baidu.com', $headers);
        $request = $request->withHeader('test', 'test1');
        $test = $request->getHeaderLine('test');
        var_dump($test);
        self::assertIsString($test);
        self::assertNotEmpty($test);
    }

    public function testWithAddedHeader()
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'test' => ['val1', 'val2']
        ];
        $request = new Request('GET', 'https://www.baidu.com', $headers);
        $request = $request->withAddedHeader('test', 'val3');
        $test = $request->getHeaderLine('test');
        var_dump($test);
        self::assertIsString($test);
    }

    public function testWithoutHeader()
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'test' => ['val1', 'val2']
        ];
        $request = new Request('GET', 'https://www.baidu.com', $headers);
        $request = $request->withoutHeader('test');
        $test = $request->getHeaderLine('test');
        var_dump($test);
        self::assertEmpty($test);
    }

    public function testGetBody()
    {
        $body = "123456789";
        $request = new Request('GET', 'https://www.baidu.com', $body);
        $body = $request->getBody();
        $body = (string)$body;
        self::assertEquals('123456789', $body);
    }

    public function testWithBody()
    {
        $request = new Request('GET', 'https://www.baidu.com');
        $body = "123456789";
        $request = $request->withBody(Stream::create($body));
        $body = $request->getBody();
        $body = (string)$body;
        self::assertEquals('123456789', $body);
    }
}
