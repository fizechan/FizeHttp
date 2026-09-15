# Client

PSR-18 HTTP 客户端，实现 `Psr\Http\Client\ClientInterface`。通过 cURL 发送 [Request](Request.md)，返回 [Response](Response.md)。

- 源码：`src/Client.php`
- 全名：`Fize\Http\Client`
- 传输：[Curl](Curl.md)
- 上层封装：[ClientSimple](ClientSimple.md)

---

## 概述

这是本库唯一的标准 PSR-18 实现。它不提供 `get()` / `post()` 便捷方法：调用方自行构造 `RequestInterface`。

默认选项：

- `CURLOPT_HEADER = true`（响应含响应头，再由本类拆分）
- `CURLOPT_RETURNTRANSFER = true`
- 超时同时设置秒与毫秒四项：`TIMEOUT` / `TIMEOUT_MS` / `CONNECTTIMEOUT` / `CONNECTTIMEOUT_MS`

析构时关闭内部 `Curl` 句柄。

---

## 构造

```php
public function __construct(
    ?string $cookie_dir = null,
    int $time_out = 30,
    int $retries = 1
)
```

| 参数 | 默认 | 说明 |
|------|------|------|
| `$cookie_dir` | `null` | Cookie 文件目录；`null` 表示不使用 Cookie |
| `$time_out` | `30` | 超时秒数 |
| `$retries` | `1` | `http_code == 0` 时的额外重试次数 |

Cookie 文件名为 `{cookie_dir}{host}.cookie`，首次使用时用 `Fize\IO\File` 创建空文件，并设置 `CURLOPT_COOKIEJAR` / `CURLOPT_COOKIEFILE`。

---

## 方法

### sendRequest

```php
public function sendRequest(RequestInterface $request): ResponseInterface
```

完整往返。流程：

1. URL 或 Method 为空 → [`RequestException`](RequestException.md)
2. 协议版本映射到 `CURLOPT_HTTP_VERSION`（`1.0` / `1.1` / `2.0`，其他为 `CURL_HTTP_VERSION_NONE`）
3. 设置 URL、自定义方法、HTTP 头
4. 可选 Cookie 文件
5. 消息体非空时写入 `CURLOPT_POSTFIELDS`
6. `exec()`；`http_code` 为 `0` 且还有重试次数则再执行
7. 按 `CURLINFO_HEADER_SIZE` 拆分头与体
8. cURL 错误：`CURLE_COULDNT_RESOLVE_HOST` / `CURLE_COULDNT_CONNECT` → [`NetworkException`](NetworkException.md)；其余 → [`ClientException`](ClientException.md)
9. 若已设置 `CURLOPT_FOLLOWLOCATION` 且响应头有 `Location`，且目标不等于当前 URL，则 `withUri` 后递归 `sendRequest`
10. 返回 `new Response($body, $http_code, $headers)`

### setOption / setOptions

```php
public function setOption(int $key, $value): void
public function setOptions(array $options): void
```

设置 cURL 选项。`setOptions()` 用 `+` 合并（数字键不能用 `array_merge`，否则会重排）。

非标量 POST 体（例如含 `CURLFile` 的数组）不要放进 PSR-7 body，应：

```php
$client->setOption(CURLOPT_POSTFIELDS, [
    'media' => new CURLFile('/path/to/file.jpg'),
]);
$request = new Request('POST', $url, null, ['Content-Type' => 'multipart/form-data']);
$response = $client->sendRequest($request);
```

更省事的做法是走 [ClientSimple](ClientSimple.md)，它会自动检测 `CURLFile`。

---

## 使用注意

1. **`$retries` 会递减且不重置**。构造 `retries = 1` 的实例在发生一次 `http_code=0` 重试后，后续请求不再重试。需要稳定重试应每次新建 `Client`，或自行在外层循环。
2. 重定向不是 cURL 原生跟随：必须自己 `setOption(CURLOPT_FOLLOWLOCATION, true)`，且解析键名为字面量 `Location`（大小写敏感）。`Location` 与当前 URL 相同则停止，避免死循环。
3. `analysisHeaders()` 把响应头解析为「名 → 单个字符串」。同名头后者覆盖前者。`Response` 构造时会再规范化为 PSR-7 数组。
4. `getBody()->getContents()` 会消耗流指针；若同一 `Request` 需要多次发送，应先 `rewind()` 或重新构建。
5. 没有 `get` / `post` 方法。需要动词 API 见 [ClientEasy](ClientEasy.md) / [ClientOnce](ClientOnce.md)。

---

## 示例

```php
use Fize\Http\Client;
use Fize\Http\Request;

$client = new Client(null, 30, 1);
$client->setOptions([
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);

$request = new Request(
    'POST',
    'http://api.fanyi.baidu.com/api/trans/vip/translate',
    http_build_query([
        'q' => '你好',
        'from' => 'zh',
        'to' => 'en',
        'appid' => '...',
        'salt' => '123456',
        'sign' => '...',
    ]),
    ['Content-Type' => 'application/x-www-form-urlencoded']
);

$response = $client->sendRequest($request);
echo $response->getStatusCode();
echo (string)$response->getBody();
```

[返回目录](index.md)
