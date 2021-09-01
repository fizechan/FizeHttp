<?php

use fize\http\Curl;
use PHPUnit\Framework\TestCase;

class TestCurl extends TestCase
{

    public function test__construct()
    {
        $curl = new Curl();
        var_export($curl);
        self::assertIsObject($curl);
    }

    public function test__destruct()
    {
        $curl = new Curl();
        var_export($curl);
        $curl->close();
        self::assertIsObject($curl);
        unset($curl);
    }

    public function testClose()
    {
        $curl = new Curl();
        var_export($curl);
        $curl->close();
        self::assertIsObject($curl);
    }

    public function testCopyHandle()
    {
        $curl = new Curl();
        $handle = $curl->copyHandle();
        self::assertIsResource($handle);
    }

    public function testErrno()
    {
        $opts = [
            CURLOPT_HEADER         => true, //返回响应头
            CURLOPT_RETURNTRANSFER => true, //指定返回结果而不直接输出
        ];
        $curl = new Curl('htps://www.baidu.com/404.php', $opts);
        $curl->exec();
        $errno = $curl->errno();
        var_export($errno);
        self::assertIsInt($errno);
    }

    public function testError()
    {
        $opts = [
            CURLOPT_HEADER         => true, //返回响应头
            CURLOPT_RETURNTRANSFER => true, //指定返回结果而不直接输出
        ];
        $curl = new Curl('htps://www.baidu.com/404.php', $opts);
        $curl->exec();
        $error = $curl->error();
        var_export($error);
        self::assertIsString($error);
    }

    public function testEscape()
    {
        $curl = new Curl();
        $str = $curl->escape('Hofbr?uhaus/München');
        var_dump($str);
        self::assertEquals('Hofbr%3Fuhaus%2FM%C3%BCnchen', $str);
    }

    public function testExec()
    {
        $opts = [
            CURLOPT_HEADER         => true, //返回响应头
            CURLOPT_RETURNTRANSFER => true, //指定返回结果而不直接输出
        ];
        $curl = new Curl('htps://www.baidu.com/404.php', $opts);
        $result = $curl->exec();
        self::assertFalse($result);
        $curl = new Curl('https://www.baidu.com/404.php', $opts);
        $result = $curl->exec();
        self::assertIsString($result);
    }

    public function testFileCreate()
    {
        $root = dirname(__DIR__);
        $cfile = Curl::fileCreate($root . '/test/TestCurl.php', 'application/octet-stream', 'file');
        self::assertInstanceOf(CURLFile::class, $cfile);
    }

    public function testGetinfo()
    {
        $opts = [
            CURLOPT_HEADER         => true, //返回响应头
            CURLOPT_RETURNTRANSFER => true, //指定返回结果而不直接输出
        ];
        $curl = new Curl('htps://www.baidu.com/404.php', $opts);
        $curl->exec();
        $info = $curl->getinfo();
        var_dump($info);
        self::assertIsArray($info);
        $url = $curl->getinfo(CURLINFO_EFFECTIVE_URL);
        var_dump($url);
        self::assertIsString($url);
    }

    public function testPause()
    {
        $opts = [
            CURLOPT_HEADER         => true, //返回响应头
            CURLOPT_RETURNTRANSFER => true, //指定返回结果而不直接输出
        ];
        $curl = new Curl('htps://www.baidu.com/404.php', $opts);
        $int = $curl->pause(CURLPAUSE_ALL);
        var_dump($int);
        self::assertIsInt($int);
    }

    public function testReset()
    {
        $opts = [
            CURLOPT_HEADER         => true, //返回响应头
            CURLOPT_RETURNTRANSFER => true, //指定返回结果而不直接输出
        ];
        $curl = new Curl('htps://www.baidu.com/404.php', $opts);
        $curl->reset();
        var_export($curl);
        self::assertIsObject($curl);
    }

    public function testSetoptArray()
    {
        $opts = [
            CURLOPT_HEADER         => true, //返回响应头
            CURLOPT_RETURNTRANSFER => true, //指定返回结果而不直接输出
        ];
        $curl = new Curl(null, $opts);
        $options = [
            CURLOPT_URL    => 'htps://www.baidu.com/404.php',
            CURLOPT_HEADER => false
        ];
        $result = $curl->setoptArray($options);
        var_export($result);
        self::assertTrue($result);
    }

    public function testSetopt()
    {
        $opts = [
            CURLOPT_HEADER         => true, //返回响应头
            CURLOPT_RETURNTRANSFER => true, //指定返回结果而不直接输出
        ];
        $curl = new Curl(null, $opts);
        $result = $curl->setopt(CURLOPT_URL, 'htps://www.baidu.com/404.php');
        var_export($result);
        self::assertTrue($result);
    }

    public function testStrError()
    {
        $error = Curl::strerror(1);
        var_dump($error);
        self::assertIsString($error);
    }

    public function testUnescape()
    {
        $curl = new Curl();
        $str = $curl->unescape('Hofbr%3Fuhaus%2FM%C3%BCnchen');
        var_dump($str);
        self::assertEquals('Hofbr?uhaus/München', $str);
    }

    public function testVersion()
    {
        $version = Curl::version();
        var_dump($version);
        self::assertIsArray($version);
    }

    public function testGetHandle()
    {
        $curl = new Curl();
        $handle = $curl->getHandle();
        self::assertIsResource($handle);
    }
}
