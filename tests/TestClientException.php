<?php

namespace Tests;

use Fize\Http\Client;
use Fize\Http\ClientException;
use Fize\Http\Request;
use PHPUnit\Framework\TestCase;

class TestClientException extends TestCase
{

    public function test()
    {
        $client = new Client();
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
        try {
            $request = new Request('POST', 'htp://api.fanyi.baidu.com/api/trans/vip/translate', $body, $headers);
            $response = $client->sendRequest($request);
            $body = $response->getBody();
            $content = (string)$body;
            echo "*****\r\n";
            echo $content;
        } catch (ClientException $e) {
            var_export($e);
            self::assertIsString($e->getMessage());
        }
    }

}
