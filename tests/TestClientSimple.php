<?php

namespace Tests;

use Fize\Http\ClientSimple;
use PHPUnit\Framework\TestCase;

class TestClientSimple extends TestCase
{

    public function testSendRequest()
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
        $response = $client->sendRequest('POST', '/api/trans/vip/translate', $body, $headers);
        $body = $response->getBody();
        $content = (string)$body;
        echo "*****\r\n";
        echo $content;
        self::assertNotEmpty($content);

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
}
