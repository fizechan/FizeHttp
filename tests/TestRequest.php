<?php


use fize\http\Request;
use PHPUnit\Framework\TestCase;

class TestRequest extends TestCase
{

    public function test__construct()
    {
        $request = new Request('GET', 'https://www.baidu.com');
        var_dump($request);
        self::assertIsObject($request);
    }

    public function testWithProtocolVersion()
    {

    }

    public function testWithoutHeader()
    {

    }

    public function testGetHeaders()
    {

    }

    public function testGetHeader()
    {

    }

    public function testWithAddedHeader()
    {

    }

    public function testWithUri()
    {

    }

    public function testHasHeader()
    {

    }

    public function testGetHeaderLine()
    {

    }

    public function testGetUri()
    {

    }

    public function testWithRequestTarget()
    {

    }

    public function testWithBody()
    {

    }

    public function testWithMethod()
    {

    }

    public function testGetMethod()
    {

    }

    public function testGetProtocolVersion()
    {

    }

    public function testWithHeader()
    {

    }



    public function testGetBody()
    {

    }

    public function testGetRequestTarget()
    {

    }
}
