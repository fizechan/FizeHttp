<?php

namespace Tests;

use Fize\Http\ClientEasy;
use PHPUnit\Framework\TestCase;

class TestClientEasy extends TestCase
{

    public function testGet()
    {
        $client = new ClientEasy('https://www.baidu.com');
        $response = $client->get('');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testPost()
    {
        $client = new ClientEasy('http://api.fanyi.baidu.com');
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
        $response = $client->post('/api/trans/vip/translate', $body, $headers);
        var_export($response);
        self::assertIsObject($response);
    }

    public function testOptions()
    {
        $client = new ClientEasy('https://www.baidu.com');
        $response = $client->options('');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testHead()
    {
        $client = new ClientEasy('https://www.baidu.com');
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $response = $client->head('', $headers);
        var_export($response);
        self::assertIsObject($response);
    }

    public function testDelete()
    {
        $client = new ClientEasy('https://www.baidu.com');
        $response = $client->delete('');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testPatch()
    {
        $client = new ClientEasy('https://www.baidu.com');
        $response = $client->patch('');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testPut()
    {
        $client = new ClientEasy('http://api.fanyi.baidu.com');
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $body = '{"q":"定制化翻译API语言方向目前只支持中文和英文。","from":"zh","to":"en","appid":"20160118000009064","salt":"123456","sign":"9ac0dad8ab7abafc710bf5a9a8516e51"}';
        $response = $client->put('/api/trans/vip/translate', $body, $headers);
        var_export($response);
        self::assertIsObject($response);
    }

    public function testTrace()
    {
        $client = new ClientEasy('https://www.baidu.com');
        $response = $client->trace('');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testMove()
    {
        $client = new ClientEasy('https://www.baidu.com');
        $response = $client->move('');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testCopy()
    {
        $client = new ClientEasy('https://www.baidu.com');
        $response = $client->copy('');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testLink()
    {
        $client = new ClientEasy('https://www.baidu.com');
        $response = $client->link('');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testUnlink()
    {
        $client = new ClientEasy('https://www.baidu.com');
        $response = $client->unlink('');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testWrapped()
    {
        $client = new ClientEasy('https://www.baidu.com');
        $response = $client->wrapped('');
        var_export($response);
        self::assertIsObject($response);
    }
}
