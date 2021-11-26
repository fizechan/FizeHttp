<?php

namespace Tests;

use Fize\Http\ClientSimple;
use PHPUnit\Framework\TestCase;

class TestClientSimple extends TestCase
{

    public function testGet()
    {
        $response = ClientSimple::get('https://www.baidu.com');
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
        $response = ClientSimple::post('http://api.fanyi.baidu.com/api/trans/vip/translate', $body, $headers);
        var_export($response);
        self::assertIsObject($response);
    }

    public function testOptions()
    {
        $response = ClientSimple::options('https://www.baidu.com');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testHead()
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $response = ClientSimple::head('https://www.baidu.com', $headers);
        var_export($response);
        self::assertIsObject($response);
    }

    public function testDelete()
    {
        $response = ClientSimple::delete('https://www.baidu.com');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testPatch()
    {
        $response = ClientSimple::patch('https://www.baidu.com');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testPut()
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $body = '{"q":"定制化翻译API语言方向目前只支持中文和英文。","from":"zh","to":"en","appid":"20160118000009064","salt":"123456","sign":"9ac0dad8ab7abafc710bf5a9a8516e51"}';
        $response = ClientSimple::put('http://api.fanyi.baidu.com/api/trans/vip/translate', $body, $headers);
        var_export($response);
        self::assertIsObject($response);
    }

    public function testTrace()
    {
        $response = ClientSimple::trace('https://www.baidu.com');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testMove()
    {
        $response = ClientSimple::move('https://www.baidu.com');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testCopy()
    {
        $response = ClientSimple::copy('https://www.baidu.com');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testLink()
    {
        $response = ClientSimple::link('https://www.baidu.com');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testUnlink()
    {
        $response = ClientSimple::unlink('https://www.baidu.com');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testWrapped()
    {
        $response = ClientSimple::wrapped('https://www.baidu.com');
        var_export($response);
        self::assertIsObject($response);
    }
}
