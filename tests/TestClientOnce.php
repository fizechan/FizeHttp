<?php

namespace Tests;

use Fize\Http\ClientOnce;
use PHPUnit\Framework\TestCase;

class TestClientOnce extends TestCase
{

    public function testGet()
    {
        $response = ClientOnce::get('https://www.baidu.com');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testPost()
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $body = [
            'q' => '定制化翻译API语言方向目前只支持中文和英文。',
            'from' => 'zh',
            'to' => 'en',
            'appid' => '20160118000009064',
            'salt' => '123456',
            'sign' => '9ac0dad8ab7abafc710bf5a9a8516e51'
        ];
        $body = http_build_query($body);
        $response = ClientOnce::post('http://api.fanyi.baidu.com/api/trans/vip/translate', $body, $headers);
        var_export($response);
        self::assertIsObject($response);
    }

    public function testOptions()
    {
        $response = ClientOnce::options('https://www.baidu.com');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testHead()
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $response = ClientOnce::head('https://www.baidu.com', $headers);
        var_export($response);
        self::assertIsObject($response);
    }

    public function testDelete()
    {
        $response = ClientOnce::delete('https://www.baidu.com');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testPatch()
    {
        $response = ClientOnce::patch('https://www.baidu.com');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testPut()
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $body = '{"q":"定制化翻译API语言方向目前只支持中文和英文。","from":"zh","to":"en","appid":"20160118000009064","salt":"123456","sign":"9ac0dad8ab7abafc710bf5a9a8516e51"}';
        $response = ClientOnce::put('http://api.fanyi.baidu.com/api/trans/vip/translate', $body, $headers);
        var_export($response);
        self::assertIsObject($response);
    }

    public function testTrace()
    {
        $response = ClientOnce::trace('https://www.baidu.com');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testMove()
    {
        $response = ClientOnce::move('https://www.baidu.com');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testCopy()
    {
        $response = ClientOnce::copy('https://www.baidu.com');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testLink()
    {
        $response = ClientOnce::link('https://www.baidu.com');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testUnlink()
    {
        $response = ClientOnce::unlink('https://www.baidu.com');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testWrapped()
    {
        $response = ClientOnce::wrapped('https://www.baidu.com');
        var_export($response);
        self::assertIsObject($response);
    }
}
