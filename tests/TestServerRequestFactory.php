<?php

use fize\http\ServerRequest;
use fize\http\ServerRequestFactory;
use PHPUnit\Framework\TestCase;

class TestServerRequestFactory extends TestCase
{

    public function testCreateServerRequest()
    {
        $factory = new ServerRequestFactory();
        $sr = $factory->createServerRequest('POST', 'https://www.baidu.com');
        var_dump($sr);
        self::assertInstanceOf(ServerRequest::class, $sr);
    }

    public function testCreateServerRequestFromGlobals()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_PROTOCOL'] = '2.0';
        $_COOKIE = ['COOKIE1' => 'cookie1'];
        $_GET = [];
        $_POST = [
            'name' => 'Fize'
        ];
        $_FILES = [
            'files' => [
                'name' => [
                    0 => 'file0.txt',
                    1 => 'file1.html',
                ],
                'type' => [
                    0 => 'text/plain',
                    1 => 'text/html',
                ],
                'tmp_name' => [
                    0 => '1111.txt',
                    1 => '2222.html',
                ],
                'size' => [
                    0 => 123456,
                    1 => 654321
                ],
                'error' => [
                    0 => 0,
                    1 => 0
                ]
            ]
        ];
        $factory = new ServerRequestFactory();
        $sr = $factory->createServerRequestFromGlobals();
        var_dump($sr);
        self::assertInstanceOf(ServerRequest::class, $sr);
    }
}
