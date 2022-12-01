<?php

namespace Tests;

use Fize\Http\ClientSimple;
use PHPUnit\Framework\TestCase;

class TestClientSimple extends TestCase
{

    public function testGet()
    {
        $client = new ClientSimple('https://www.baidu.com');
        $response = $client->get('');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testPost()
    {
        $client = new ClientSimple('http://api.fanyi.baidu.com');
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
        $client = new ClientSimple('https://www.baidu.com');
        $response = $client->options('');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testHead()
    {
        $client = new ClientSimple('https://www.baidu.com');
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $response = $client->head('', $headers);
        var_export($response);
        self::assertIsObject($response);
    }

    public function testDelete()
    {
        $client = new ClientSimple('https://www.baidu.com');
        $response = $client->delete('');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testPatch()
    {
        $client = new ClientSimple('https://www.baidu.com');
        $response = $client->patch('');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testPut()
    {
        $client = new ClientSimple('http://api.fanyi.baidu.com');
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
        $client = new ClientSimple('https://www.baidu.com');
        $response = $client->trace('');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testMove()
    {
        $client = new ClientSimple('https://www.baidu.com');
        $response = $client->move('');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testCopy()
    {
        $client = new ClientSimple('https://www.baidu.com');
        $response = $client->copy('');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testLink()
    {
        $client = new ClientSimple('https://www.baidu.com');
        $response = $client->link('');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testUnlink()
    {
        $client = new ClientSimple('https://www.baidu.com');
        $response = $client->unlink('');
        var_export($response);
        self::assertIsObject($response);
    }

    public function testWrapped()
    {
        $client = new ClientSimple('https://www.baidu.com');
        $response = $client->wrapped('');
        var_export($response);
        self::assertIsObject($response);
    }
}
