# ClientEasy

实例化的 HTTP 动词客户端。内部持有 [ClientSimple](ClientSimple.md)。

- 源码：`src/ClientEasy.php`
- 全名：`Fize\Http\ClientEasy`
- 静态对照：[ClientOnce](ClientOnce.md)

---

## 概述

同一实例可复用 host、Cookie 目录、超时与 cURL 选项。适合对同一 API 根地址发起多次请求。

每个动词方法：若传入 `$options` 则先 `setOptions()`，再交给 `ClientSimple::sendRequest()`。`head()` 额外设置 `CURLOPT_NOBODY = true`，避免等待响应体导致超时。

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

参数含义与 [ClientSimple](ClientSimple.md) 相同。`$host` 为空时，方法里的 `$uri` 应写完整 URL。

---

## 方法

除特别说明外，均返回 `Psr\Http\Message\ResponseInterface`。

| 方法 | 签名要点 |
|------|----------|
| `get` | `(string $uri, array $headers = [], array $options = [])` |
| `post` | `(string $uri, $body, array $headers = [], array $options = [])`，`$body` 为 `string\|array` |
| `put` | `(string $uri, $body = '', array $headers = [], array $options = [])` |
| `patch` | `(string $uri, array $headers = [], array $options = [])`，body 固定为 `null` |
| `delete` | `(string $uri, array $headers = [], array $options = [])` |
| `head` | 同上，并设置 `CURLOPT_NOBODY` |
| `options` | 同上 |
| `trace` | 同上 |
| `move` / `copy` | WebDAV |
| `link` / `unlink` | |
| `wrapped` | |

`post()` / `put()` 的数组 body 由 [ClientSimple](ClientSimple.md) 序列化或按 `CURLFile` 上传。

**注意**：`patch()` 当前不接受 body。需要 PATCH 带体时，使用 `ClientSimple::sendRequest('PATCH', ...)` 或 [Client](Client.md)。

`$options` 设置后会留在内部客户端上，影响后续请求。

---

## 示例

```php
use Fize\Http\ClientEasy;

$client = new ClientEasy('https://www.baidu.com');
$response = $client->get('');
$response = $client->head('');

$client = new ClientEasy('http://api.fanyi.baidu.com');
$response = $client->post(
    '/api/trans/vip/translate',
    http_build_query(['q' => '你好', 'from' => 'zh', 'to' => 'en']),
    ['Content-Type' => 'application/x-www-form-urlencoded']
);

$client = new ClientEasy();
$response = $client->get('https://www.example.com/path');
```

[返回目录](index.md)
