<?php

namespace Fize\Http;

use CURLFile;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * HTTP简易客户端
 */
class ClientSimple
{

    /**
     * @var string 主机名
     */
    protected $host;

    /**
     * @var Client HTTP客户端
     */
    protected $client;

    /**
     * 初始化
     * @param string      $host       主机名
     * @param string|null $cookie_dir 指定保存COOKIE文件的路径，默认null表示不使用COOKIE
     * @param int         $time_out   设定超时时间,默认30秒
     * @param int         $retries    curl重试次数
     * @param array       $opts       CURL选项
     */
    public function __construct(string $host, string $cookie_dir = null, int $time_out = 30, int $retries = 1, array $opts = [])
    {
        $this->host = $host;
        $this->client = new Client($cookie_dir, $time_out, $retries);
        if ($opts) {
            $this->client->setOptions($opts);
        }
    }

    /**
     * 发送HTTP请求
     * @param string              $method  请求方式
     * @param string|UriInterface $uri     请求URI
     * @param string|array        $body    请求体
     * @param array               $headers 报头信息
     * @return ResponseInterface 返回响应对象
     */
    public function sendRequest(string $method, $uri, $body = null, array $headers = []): ResponseInterface
    {
        $url = $this->host . $uri;
        $data = null;
        if (is_string($body)) {
            $data = $body;
        } elseif (self::isUploadFile($body)) {
            $data = $body;  // 需要POST上传文件时直接传递数组
        } elseif (!empty($body)) {
            $data = http_build_query($body);
        }
        if (!is_null($data)) {
            $this->client->setOption(CURLOPT_POSTFIELDS, $data);
        }

        if (is_array($body)) {
            $body = null;  // 使用CURL直接传递body
        }

        $request = new Request($method, $url, $body, $headers);
        return $this->client->sendRequest($request);
    }

    /**
     * 判断上传的东西是否包含文件上传
     * @param mixed $body 请求体
     * @return bool
     */
    public static function isUploadFile($body): bool
    {
        if (!is_array($body)) {
            return false;
        }
        foreach ($body as $val) {
            if ($val instanceof CURLFile) {
                return true;
            }
        }
        return false;
    }
}
