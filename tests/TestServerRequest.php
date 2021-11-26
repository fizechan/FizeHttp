<?php

namespace Tests;

use Fize\Http\ServerRequest;
use Fize\Http\Stream;
use Fize\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

class TestServerRequest extends TestCase
{

    public function test__construct()
    {
        $sr = new ServerRequest('GET', 'https://www.baidu.com');
        var_dump($sr);
        self::assertInstanceOf(ServerRequest::class, $sr);
    }

    public function testGetServerParams()
    {
        $serverParams = ['test' => '1', 'myKey' => 'myVal'];
        $sr = new ServerRequest('GET', 'https://www.baidu.com', null, [], $serverParams);
        var_dump($sr->getServerParams());
        self::assertEquals($serverParams, $sr->getServerParams());
    }

    public function testGetCookieParams()
    {
        $sr = new ServerRequest('GET', 'https://www.baidu.com');
        $cp1 = $sr->getCookieParams();
        self::assertEmpty($cp1);
        $cookies = ['key1' => 'value1'];
        $sr = $sr->withCookieParams($cookies);
        $cp2 = $sr->getCookieParams();
        var_dump($cp2);
        self::assertNotEquals($cp2, $cp1);
    }

    public function testWithCookieParams()
    {
        $sr = new ServerRequest('GET', 'https://www.baidu.com');
        $cp1 = $sr->getCookieParams();
        self::assertEmpty($cp1);
        $cookies = ['key1' => 'value1'];
        $sr = $sr->withCookieParams($cookies);
        $cp2 = $sr->getCookieParams();
        var_dump($cp2);
        self::assertNotEquals($cp2, $cp1);
        self::assertNotEmpty($cp2);
    }

    public function testGetQueryParams()
    {
        $sr = new ServerRequest('GET', 'https://www.baidu.com');
        $qp1 = $sr->getQueryParams();
        self::assertEmpty($qp1);
        $querys = ['key1' => 'value1'];
        $sr = $sr->withQueryParams($querys);
        $qp2 = $sr->getQueryParams();
        var_dump($qp2);
        self::assertNotEquals($qp2, $qp1);
        self::assertNotEmpty($qp2);
    }

    public function testWithQueryParams()
    {
        $sr = new ServerRequest('GET', 'https://www.baidu.com');
        $qp1 = $sr->getQueryParams();
        self::assertEmpty($qp1);
        $querys = ['key1' => 'value1'];
        $sr = $sr->withQueryParams($querys);
        $qp2 = $sr->getQueryParams();
        var_dump($qp2);
        self::assertNotEquals($qp2, $qp1);
        self::assertNotEmpty($qp2);
    }

    public function testGetUploadedFiles()
    {
        $sr = new ServerRequest('GET', 'https://www.baidu.com');
        $upfs1 = $sr->getUploadedFiles();
        self::assertEmpty($upfs1);
        $ups1 = new Stream(fopen(__FILE__, 'r'));
        $upf1 = new UploadedFile($ups1, $ups1->getSize(), UPLOAD_ERR_OK);
        $upf2 = new UploadedFile($ups1, $ups1->getSize(), UPLOAD_ERR_OK);
        $upfs2 = [$upf1, $upf2];
        $sr = $sr->withUploadedFiles($upfs2);
        self::assertEquals($sr->getUploadedFiles(), $upfs2);
    }

    public function testWithUploadedFiles()
    {
        $sr = new ServerRequest('GET', 'https://www.baidu.com');
        $upfs1 = $sr->getUploadedFiles();
        self::assertEmpty($upfs1);
        $ups1 = new Stream(fopen(__FILE__, 'r'));
        $upf1 = new UploadedFile($ups1, $ups1->getSize(), UPLOAD_ERR_OK);
        $upf2 = new UploadedFile($ups1, $ups1->getSize(), UPLOAD_ERR_OK);
        $upfs2 = [$upf1, $upf2];
        $sr = $sr->withUploadedFiles($upfs2);
        self::assertEquals($sr->getUploadedFiles(), $upfs2);
    }

    public function testGetParsedBody()
    {
        $sr = new ServerRequest('GET', 'https://www.baidu.com', 'IS OK!');
        $pbody1 = $sr->getParsedBody();
        self::assertEmpty($pbody1);
        $body = $sr->getBody();
        self::assertNotEmpty($body);
        self::assertEquals('IS OK!', $body);
        $data = [
            'test' => 'val'
        ];
        $pbody2 = $sr->withParsedBody($data)->getParsedBody();
        self::assertEquals($data, $pbody2);
    }

    public function testWithParsedBody()
    {
        $sr = new ServerRequest('GET', 'https://www.baidu.com', 'IS OK!');
        $pbody1 = $sr->getParsedBody();
        self::assertEmpty($pbody1);
        $body = $sr->getBody();
        self::assertNotEmpty($body);
        self::assertEquals('IS OK!', $body);
        $data = [
            'test' => 'val'
        ];
        $pbody2 = $sr->withParsedBody($data)->getParsedBody();
        self::assertEquals($data, $pbody2);
    }

    public function testGetAttributes()
    {
        $sr = new ServerRequest('GET', 'https://www.baidu.com');
        $attrs = $sr->getAttributes();
        self::assertEmpty($attrs);
        $sr = $sr->withAttribute('key1', 'name1');
        $attrs = $sr->getAttributes();
        self::assertCount(1, $attrs);
    }

    public function testGetAttribute()
    {
        $sr = new ServerRequest('GET', 'https://www.baidu.com');
        $val1 = $sr->getAttribute('key1', 'OK');
        self::assertEquals('OK', $val1);
        $val2 = $sr->getAttribute('key2');
        self::assertNull($val2);
        $sr = $sr->withAttribute('key3', 'val3');
        $val3 = $sr->getAttribute('key3');
        self::assertEquals('val3', $val3);
    }

    public function testWithAttribute()
    {
        $sr = new ServerRequest('GET', 'https://www.baidu.com');
        $val1 = $sr->getAttribute('key1', 'OK');
        self::assertEquals('OK', $val1);
        $val2 = $sr->getAttribute('key2');
        self::assertNull($val2);
        $sr = $sr->withAttribute('key3', 'val3');
        $val3 = $sr->getAttribute('key3');
        self::assertEquals('val3', $val3);
    }

    public function testWithoutAttribute()
    {
        $sr = new ServerRequest('GET', 'https://www.baidu.com');
        $sr = $sr->withAttribute('key3', 'val3');
        $val3 = $sr->getAttribute('key3');
        self::assertEquals('val3', $val3);
        $sr = $sr->withoutAttribute('key3');
        self::assertNotEquals($val3, $sr->getAttribute('key3'));
    }
}
