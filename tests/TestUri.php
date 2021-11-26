<?php

namespace Tests;

use Fize\Http\Uri;
use PHPUnit\Framework\TestCase;

class TestUri extends TestCase
{

    public function test__construct()
    {
        $uri = new Uri();
        var_dump($uri);
        self::assertIsObject($uri);
        $uri = new Uri('https://www.baidu.com?wd=test#t123');
        var_dump($uri);
        self::assertIsObject($uri);
    }

    public function test__toString()
    {
        $uri = new Uri('https://www.baidu.com?wd=搜索一下#t123');
        $url = (string)$uri;
        var_dump($url);
        self::assertIsString($url);
    }

    public function testComposeComponents()
    {
        $scheme = 'https';
        $authority = 'www.baidu.com:88';
        $path = '/test1/test2';
        $query = 'wd='.urlencode('中国').'&kkk=123';
        $fragment = 'top';
        $url = Uri::composeComponents($scheme, $authority, $path, $query, $fragment);
        var_dump($url);
        self::assertIsString($url);
    }

    public function testGetScheme()
    {
        $uri = new Uri('https://www.baidu.com?wd=搜索一下#t123');
        $scheme = $uri->getScheme();
        self::assertEquals('https', $scheme);
    }

    public function testGetAuthority()
    {
        $uri = new Uri('https://www.baidu.com:88/test1/test2?wd=搜索一下#t123');
        $authority = $uri->getAuthority();
        self::assertEquals('www.baidu.com:88', $authority);
        $uri = new Uri('http://username:password@hostname/path?arg=value#anchor');
        $authority = $uri->getAuthority();
        var_dump($authority);
        self::assertEquals('username:password@hostname', $authority);
    }

    public function testGetUserInfo()
    {
        $uri = new Uri('https://www.baidu.com:88/test1/test2?wd=搜索一下#t123');
        $userinfo = $uri->getUserInfo();
        self::assertEquals('', $userinfo);

        $uri = new Uri('http://username:password@hostname/path?arg=value#anchor');
        $userinfo = $uri->getUserInfo();
        self::assertEquals('username:password', $userinfo);
    }

    public function testGetHost()
    {
        $uri = new Uri('https://www.baidu.com:88/test1/test2?wd=搜索一下#t123');
        $host = $uri->getHost();
        self::assertEquals('www.baidu.com', $host);
    }

    public function testGetPort()
    {
        $uri = new Uri('https://www.baidu.com:88/test1/test2?wd=搜索一下#t123');
        $port = $uri->getPort();
        self::assertEquals(88, $port);

        $uri = new Uri('https://www.baidu.com/test1/test2?wd=搜索一下#t123');
        $port = $uri->getPort();
        self::assertEquals(null, $port);
    }

    public function testGetPath()
    {
        $uri = new Uri('https://www.baidu.com/test1/test2?wd=搜索一下#t123');
        $path = $uri->getPath();
        self::assertEquals('/test1/test2', $path);
    }

    public function testGetQuery()
    {
        $uri = new Uri('https://www.baidu.com/test1/test2?wd=搜索一下#t123');
        $query = $uri->getQuery();
        var_dump($query);
        self::assertEquals('wd='.urlencode('搜索一下'), $query);
    }

    public function testGetFragment()
    {
        $uri = new Uri('https://www.baidu.com/test1/test2?wd=搜索一下#t123');
        $fragment = $uri->getFragment();
        self::assertEquals('t123', $fragment);
    }

    public function testWithScheme()
    {
        $uri = new Uri('http://www.baidu.com');
        $uri = $uri->withScheme('https');
        $uri = (string)$uri;
        self::assertEquals('https://www.baidu.com', $uri);
    }

    public function testWithUserInfo()
    {
        $uri = new Uri('http://www.baidu.com');
        $uri = $uri->withUserInfo('fize', '123456');
        $uri = (string)$uri;
        self::assertEquals('http://fize:123456@www.baidu.com', $uri);
    }

    public function testWithHost()
    {
        $uri = new Uri('http://www.baidu.com');
        $uri = $uri->withHost('www.test.com');
        $uri = (string)$uri;
        self::assertEquals('http://www.test.com', $uri);
    }

    public function testWithPort()
    {
        $uri = new Uri('http://www.baidu.com');
        $uri = $uri->withPort(99);
        $uri = (string)$uri;
        self::assertEquals('http://www.baidu.com:99', $uri);
    }

    public function testWithPath()
    {
        $uri = new Uri('http://www.baidu.com');
        $uri = $uri->withPath('/test1/test2');
        $uri = (string)$uri;
        self::assertEquals('http://www.baidu.com/test1/test2', $uri);
    }

    public function testWithQuery()
    {
        $uri = new Uri('http://www.baidu.com');
        $uri = $uri->withQuery('wd=test');
        $uri = (string)$uri;
        self::assertEquals('http://www.baidu.com?wd=test', $uri);
    }

    public function testWithFragment()
    {
        $uri = new Uri('http://www.baidu.com');
        $uri = $uri->withFragment('top');
        $uri = (string)$uri;
        self::assertEquals('http://www.baidu.com#top', $uri);
    }

    public function testIsAbsolute()
    {
        $uri = new Uri('http://www.baidu.com');
        $isabs = $uri->isAbsolute();
        self::assertTrue($isabs);
    }

    public function testIsNetworkPathReference()
    {
        $uri = new Uri('//fize:123456@www.baidu.com');
        $is = $uri->isNetworkPathReference();
        self::assertTrue($is);
    }

    public function testIsAbsolutePathReference()
    {
        $uri = new Uri('/test');
        $is = $uri->isAbsolutePathReference();
        self::assertTrue($is);
    }

    public function testIsRelativePathReference()
    {
        $uri = new Uri('../test');
        $is = $uri->isRelativePathReference();
        self::assertTrue($is);
    }

    public function testIsSameDocumentReference()
    {
        $uri1 = new Uri('http://www.baidu.com/test1/test2?kd=123#top');
        $uri2 = new Uri('http://www.baidu.com/test1/test2?kd=123');
        $is = Uri::isSameDocumentReference($uri1, $uri2);
        self::assertTrue($is);
    }

    public function testIsDefaultPort()
    {
        $uri = new Uri('http://www.baidu.com/test1/test2?kd=123');
        $is = $uri->isDefaultPort();
        self::assertTrue($is);
    }

    public function testRemoveDotSegments()
    {
        $path = '/temp/../test1/test2';
        $path = Uri::removeDotSegments($path);
        var_dump($path);
        self::assertEquals('/test1/test2', $path);
    }

    public function testWithoutQueryParam()
    {
        $uri = new Uri('http://www.baidu.com?kw1=str1&kwd2=str2');
        $uri = $uri->withoutQueryParam('kwd2');
        $uri = (string)$uri;
        self::assertEquals('http://www.baidu.com?kw1=str1', $uri);
    }

    public function testWithQueryParam()
    {
        $uri = new Uri('http://www.baidu.com');
        $uri = $uri->withQueryParam('kw1', 'str1');
        $uri = (string)$uri;
        self::assertEquals('http://www.baidu.com?kw1=str1', $uri);
    }

    public function testWithQueryParams()
    {
        $QueryValues = [
            'kw1' => 'str1',
            'sk2' => 'st23'
        ];
        $uri = new Uri('http://www.baidu.com');
        $uri = $uri->withQueryParams($QueryValues);
        self::assertEquals('http://www.baidu.com?kw1=str1&sk2=st23', $uri);
    }

    public function testFromParts()
    {
        $parts = [
            'scheme' => 'https',
            'host' => 'www.baidu.com',
            'path' => '/test1/test2',
            'query' => 'kw=123'
        ];
        $uri = Uri::fromParts($parts);
        $uri = (string)$uri;
        self::assertEquals('https://www.baidu.com/test1/test2?kw=123', $uri);
    }

    public function testNormalize()
    {
        $parts = [
            'scheme' => 'https',
            'host' => 'www.baidu.com',
            'path' => '/test1/test2',
            'query' => 'kw=哈哈哈'
        ];
        $uri = Uri::fromParts($parts);
        $uri = $uri->normalize();
        $uri = (string)$uri;
        var_dump($uri);
        self::assertEquals('https://www.baidu.com/test1/test2?kw=' . urlencode('哈哈哈'), $uri);
    }

    public function testResolve()
    {
        $base = new Uri('https://www.baidu.com/test1');
        $rel = new Uri('https://www.baidu.com/../test2/test3?kw=123');
        $uri = Uri::resolve($base, $rel);
        $uri = (string)$uri;
        var_dump($uri);
        self::assertEquals('https://www.baidu.com/test2/test3?kw=123', $uri);
    }

    public function testRelativize()
    {
        $base = new Uri('https://www.baidu.com/test1');
        $target = new Uri('https://www.baidu.com/../test2/test3?kw=123');
        $uri = Uri::relativize($base, $target);
        $uri = (string)$uri;
        var_dump($uri);
        self::assertEquals('../test2/test3?kw=123', $uri);
    }
}
