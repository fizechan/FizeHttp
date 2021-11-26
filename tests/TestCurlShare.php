<?php

namespace Tests;

use Fize\Http\CurlShare;
use PHPUnit\Framework\TestCase;

class TestCurlShare extends TestCase
{

    public function test__construct()
    {
        $curl = new CurlShare();
        var_export($curl);
        self::assertIsObject($curl);
    }

    public function test__destruct()
    {
        $curl = new CurlShare();
        var_export($curl);
        self::assertIsObject($curl);
        unset($curl);
    }

    public function testClose()
    {
        $curl = new CurlShare();
        $curl->close();
        var_export($curl);
        self::assertIsObject($curl);
        unset($curl);
    }

    public function testErrno()
    {
        $curl = new CurlShare();
        $errno = $curl->errno();
        var_dump($errno);
        self::assertIsInt($errno);
    }

    public function testSetopt()
    {
        $curl = new CurlShare();
        $rst = $curl->setopt(CURLSHOPT_SHARE, CURL_LOCK_DATA_COOKIE);
        var_dump($rst);
        self::assertTrue($rst);
    }

    public function testStrerror()
    {
        $error = CurlShare::strerror(1);
        var_dump($error);
        self::assertIsString($error);
    }

    public function testGetHandle()
    {
        $curl = new CurlShare();
        $handle = $curl->getHandle();
        var_dump($handle);
        self::assertIsResource($handle);
    }
}
