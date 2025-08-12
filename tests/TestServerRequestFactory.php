<?php

namespace Tests;

use Fize\Http\ServerRequestFactory;
use Fize\Http\UploadedFileFactory;
use PHPUnit\Framework\TestCase;

class TestServerRequestFactory extends TestCase
{

    public function testCreateServerRequest()
    {
        $factory = new ServerRequestFactory();
        $sr = $factory->createServerRequest('POST', 'https://www.baidu.com');
        var_dump($sr);
        self::assertNotNull($sr);
    }

    public function testCreateServerRequestFromGlobals()
    {
        global $_SERVER, $_COOKIE, $_GET, $_POST, $_FILES;
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
        self::assertNotNull($sr);
    }

    public function testSetGlobals()
    {
        $factory = new ServerRequestFactory();
        $sr = $factory->createServerRequest('POST', 'https://www.baidu.com');
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
                    0 => __FILE__,
                    1 => __FILE__,
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
        $sr = $sr->withUploadedFiles((new UploadedFileFactory())->createUploadedFilesFromSpec($_FILES));
        $sr = $sr->withQueryParams(['key1' => 'val1', 'val2' => 'data2', 'valu3' => 'data3']);
        $sr = $sr->withParsedBody(['val1' => 'data1', 'val2' => 'data22']);
        ServerRequestFactory::setGlobals($sr);
        var_dump($_SERVER, $_COOKIE, $_GET, $_POST, $_REQUEST);
        self::assertNotNull($sr);
    }
}
