<?php

namespace Tests;

use Fize\Http\Client;
use Fize\Http\Request;
use PHPUnit\Framework\TestCase;

class TestClient extends TestCase
{

    public function test__construct()
    {
        $client = new Client();
        var_dump($client);
        self::assertIsObject($client);
    }

    public function test__destruct()
    {
        $client = new Client();
        var_dump($client);
        self::assertIsObject($client);
        unset($client);
    }

    public function testSendRequest()
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
        $request = new Request('POST', 'http://api.fanyi.baidu.com/api/trans/vip/translate', $body, $headers);
        $response = $client->sendRequest($request);
        $body = $response->getBody();
        $content = (string)$body;
        echo "*****\r\n";
        echo $content;
        self::assertNotNull($response);

//        // 测试文件上传
//        $file = 'H:\web\shangyi\www.sygame.loc\src\static\index\bafang\index\image\bg.jpg';
//        $body = [
//            'media' => new CURLFile(realpath($file))
//        ];
//        $access_token = "33_h4CnFEaZMdfriRK_7CY5exWh6Aqpxsa9jO3rLjwn7XjyhDHb7AXGZ6ZFE2Da0kPjyqYAjZZjPFC5yUyhkPmg85bovptWv9rNNZircBs_M7ap_ITpqTdUhcuz95CbXJLrRC2tEt-5clSd71NuLERdADAFYL";
//        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type=image";
//        $request = new Request('POST', $url, null, ['Content-type' => 'multipart/form-data']);
//        $client = new Client();
////        $client->setOptions([
////            CURLOPT_POST => true,
////        ]);
//        $client->setOption(CURLOPT_POSTFIELDS, $body);  // 值为非标量时使用setOption方法
//
//        $response = $client->sendRequest($request);
//        var_dump($response);
//        $body = $response->getBody();
//        $content = (string)$body;
//        echo "*****\r\n";
//        echo $content;
//        self::assertInstanceOf(Response::class, $response);
    }

    public function testSetOption()
    {
        $client = new Client();
        $client->setOption(CURLOPT_SSLVERSION, 2);
        var_dump($client);
        self::assertIsObject($client);
    }

    public function testSetOptions()
    {
        $client = new Client();
        $client->setOptions([
            CURLOPT_SSL_VERIFYPEER    => false, //禁止cURL验证对等证书
            CURLOPT_SSL_VERIFYHOST    => false, //不检查服务器SSL证书中是否存在一个公用名
            CURLOPT_SSLVERSION        => 1, //使用CURL_SSLVERSION_TLSv1，在 SSLv2 和 SSLv3 中有弱点存在。
        ]);
        var_dump($client);
        self::assertIsObject($client);
    }
}
