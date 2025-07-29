<?php

namespace Tests;

use Fize\Http\Request;
use Fize\Http\Response;
use Fize\Http\StreamFactory;
use PHPUnit\Framework\TestCase;

class TestMessage extends TestCase
{

    public function testGetProtocolVersion()
    {
        $request = new Request('GET', 'https://www.baidu.com/');
        $pv = $request->getProtocolVersion();
        var_dump($pv);
        self::assertEquals('1.1', $pv);
        $response = new Response(null, 200, [], '2.0');
        $pv = $response->getProtocolVersion();
        var_dump($pv);
        self::assertEquals('2.0', $pv);
    }

    public function testWithProtocolVersion()
    {
        $request = new Request('GET', 'https://www.baidu.com/');
        $request = $request->withProtocolVersion('2.0');
        var_dump($request);
        self::assertInstanceOf(Request::class, $request);
        $pv = $request->getProtocolVersion();
        var_dump($pv);
        self::assertEquals('2.0', $pv);
        $uri = $request->getUri();
        var_dump($uri);

        $response = new Response();
        $response = $response->withProtocolVersion('2.0');
        var_dump($response);
        self::assertInstanceOf(Response::class, $response);
        $pv = $response->getProtocolVersion();
        var_dump($pv);
        self::assertEquals('2.0', $pv);
        $statusCode = $response->getStatusCode();
        var_dump($statusCode);
    }

    public function testGetHeaders()
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $request = new Request('GET', 'https://www.baidu.com', null, $headers);
        $headers = $request->getHeaders();
        var_dump($headers);
        self::assertIsArray($headers);

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $response = new Response('这是响应内容', 200, $headers);
        $headers = $response->getHeaders();
        var_dump($headers);
        self::assertIsArray($headers);
    }

    public function testHasHeader()
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $request = new Request('GET', 'https://www.baidu.com', null, $headers);
        $has1 = $request->hasHeader('Content-Type');
        self::assertTrue($has1);
        $has2 = $request->hasHeader('CONTENT-TYPE');
        self::assertTrue($has2);  // 不区分大小写
        $has3 = $request->hasHeader('Content-Type2');
        self::assertFalse($has3);
    }

    public function testGetHeader()
    {
        $headers = [
            'Accept' => ['text/html', 'application/xhtml+xml', 'application/xml;q=0.9', 'image/webp', '*/*;q=0.8'],
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $request = new Request('GET', 'https://www.baidu.com', null, $headers);
        $accept = $request->getHeader('Accept');
        var_dump($accept);
        self::assertIsArray($accept);
        self::assertGreaterThan(1, count($accept));
        $ct = $request->getHeader('content-type');  // 不区分大小写
        var_dump($ct);
        self::assertIsArray($ct);
        self::assertCount(1, $ct);
    }

    public function testGetHeaderLine()
    {
        $request = new Request('GET', 'https://www.baidu.com');
        $request = $request->withHeader('Accept', ['text/html','application/xhtml+xml','application/xml;q=0.9','image/webp','*/*;q=0.8']);
        $headline = $request->getHeaderLine('accept');  // 不区分大小写
        var_dump($headline);
        self::assertIsString($headline);
    }

    public function testWithHeader()
    {
        $request = new Request('GET', 'https://www.baidu.com');
        $request = $request->withHeader('Accept', ['text/html','application/xhtml+xml','application/xml;q=0.9','image/webp','*/*;q=0.8']);
        $headers = $request->getHeaders();
        var_dump($headers);
        self::assertIsArray($headers);
    }

    public function testWithAddedHeader()
    {
        $request = new Request('GET', 'https://www.baidu.com');
        $header1s = $request->getHeader('Accept');
        var_dump($header1s);
        self::assertIsArray($header1s);

        $request = $request->withAddedHeader('accept', 'text/html');
        $header2s = $request->getHeader('Accept');
        var_dump($header2s);
        self::assertNotEquals($header1s, $header2s);

        $request = $request->withAddedHeader('accept', ['text/html']);  // 重复项添加时不会报错。
        $header3s = $request->getHeader('Accept');
        var_dump($header3s);
        self::assertEquals($header2s, $header3s);

        $request = $request->withAddedHeader('Accept', ['text/html','application/xhtml+xml','application/xml;q=0.9','image/webp','*/*;q=0.8']);
        $header4s = $request->getHeader('Accept');
        var_dump($header4s);
        self::assertNotEquals($header3s, $header4s);
    }

    public function testWithoutHeader()
    {
        $request = new Request('GET', 'https://www.baidu.com');
        $request = $request->withHeader('Accept', ['text/html','application/xhtml+xml','application/xml;q=0.9','image/webp','*/*;q=0.8']);
        $request = $request->withoutHeader('accept');
        $accept = $request->getHeader('accept');
        var_dump($accept);
        self::assertEmpty($accept);
    }

    public function testGetBody()
    {
        $body = "123456789";
        $request = new Request('GET', 'https://www.baidu.com', $body);
        $body = $request->getBody();
        self::assertNotNull($body);
        $body = (string)$body;
        self::assertEquals('123456789', $body);
    }

    public function testWithBody()
    {
        $request = new Request('GET', 'https://www.baidu.com');
        $body = "123456789";
        $factory = new StreamFactory();
        $request = $request->withBody($factory->createStream($body));
        $body = $request->getBody();
        $body = (string)$body;
        self::assertEquals('123456789', $body);
    }
}
