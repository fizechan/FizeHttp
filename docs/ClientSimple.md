# ClientSimple

简易 HTTP 客户端：拼接主机前缀、自动序列化 body、识别文件上传。内部持有 [Client](Client.md)。

- 源码：`src/ClientSimple.php`
- 全名：`Fize\Http\ClientSimple`
- 被 [ClientEasy](ClientEasy.md)、[ClientOnce](ClientOnce.md) 使用

---

## 概述

`sendRequest()` 的参数是「方法 + URI + 原始 body」，而不是 PSR-7 `RequestInterface`。适合在固定 API 根地址上发请求，又不想每次手写 `http_build_query`。

---

## 构造

```php
public function __construct(
    string $host = '',
    ?string $cookie_dir = null,
    int $time_out = 30,
    int $retries = 1,
    array $opts = []
)
```

| 参数 | 说明 |
|------|------|
| `$host` | 前缀，与 `$uri` 直接拼接，**不会自动补 `/`** |
| `$cookie_dir` / `$time_out` / `$retries` | 传给内部 `Client` |
| `$opts` | 初始 cURL 选项 |

`ClientOnce` 使用 `$host = ''`，因此 `$uri` 必须是完整 URL。

---

## 方法

### sendRequest

```php
public function sendRequest(
    string $method,
    $uri,                 // string|UriInterface
    $body = null,         // string|array|null
    array $headers = []
): ResponseInterface
```

实际 URL = `$this->host . $uri`。

body 处理：

| `$body` | 行为 |
|---------|------|
| `string` | 原样作为 POSTFIELDS，并写入 Request body |
| 数组且含 `CURLFile` | 原样交给 `CURLOPT_POSTFIELDS`（multipart）；Request body 置 `null` |
| 其他非空（通常是数组） | `http_build_query` 后作为 POSTFIELDS；Request body 置 `null` |
| `null` / 空 | 不设置 POSTFIELDS |

数组 body 依赖已设置的 `CURLOPT_POSTFIELDS`，而不是 PSR-7 消息体。这与纯 [Client](Client.md) 路径不同。

### setOption / setOptions

代理到内部 `Client`。

### isUploadFile

```php
public static function isUploadFile($body): bool
```

`$body` 为数组且任一值为 `CURLFile` 实例时返回 `true`。不递归检查嵌套数组。

---

## 示例

```php
use Fize\Http\ClientSimple;

$client = new ClientSimple('http://api.fanyi.baidu.com');
$response = $client->sendRequest(
    'POST',
    '/api/trans/vip/translate',
    http_build_query(['q' => '你好', 'from' => 'zh', 'to' => 'en']),
    ['Content-Type' => 'application/x-www-form-urlencoded']
);

// 数组会自动 http_build_query
$response = $client->sendRequest('POST', '/api', ['name' => 'fize']);

// 文件上传
$response = $client->sendRequest('POST', '/upload', [
    'file' => new CURLFile('/path/to/a.jpg'),
]);
```

[返回目录](index.md)
