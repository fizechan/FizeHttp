# ClientMulti

基于 `curl_multi_*` 的并发 HTTP 客户端。发送多个 PSR-7 请求，按相同键返回响应数组。

- 源码：`src/ClientMulti.php`
- 全名：`Fize\Http\ClientMulti`
- 传输：[CurlMulti](CurlMulti.md)、[Curl](Curl.md)

---

## 概述

与 [Client](Client.md) 的差异：

| 项目 | Client | ClientMulti |
|------|--------|-------------|
| 执行模型 | 串行单句柄 | `curl_multi` 并发 |
| 失败 | 抛异常 | 对应项为 `null`，异常写入引用数组 |
| 重试 | `http_code=0` 时递减重试 | **`$retries` 已声明未使用** |
| 重定向 | 可选手动 follow | **不跟随** |
| 队列 | 无 | `addRequest` 后 `send`，发送完清空 |

不实现 PSR-18 `ClientInterface`。

---

## 构造

```php
public function __construct(
    ?string $cookie_dir = null,
    int $time_out = 30,
    int $retries = 1
)
```

`$retries` 目前没有效果。Cookie 规则与 `Client` 相同：`{cookie_dir}{host}.cookie`。

---

## 方法

### addRequest

```php
public function addRequest(RequestInterface $request, $key = null)
```

加入队列。`$key` 为 `null` 时使用自动数字索引，返回该索引；否则使用给定键并原样返回。

### send

```php
public function send(array &$exceptions = []): array
```

并发执行当前队列。

- 返回数组键与请求键一致
- 成功值为 [`Response`](Response.md)，失败为 `null`
- 网络类错误放入 `$exceptions[$key]` 为 [`NetworkException`](NetworkException.md)，其他 cURL 错误为 [`ClientException`](ClientException.md)
- 队列为空时返回 `[]`
- **发送后清空队列**，避免重复发送

### sendRequests

```php
public function sendRequests(array $requests, array &$exceptions = []): array
```

用传入数组**替换**当前队列，再 `send()`。

---

## 受保护方法

### buildHandle

```php
protected function buildHandle(RequestInterface $request): Curl
```

按请求配置单句柄：协议版本、方法、头、超时、Cookie、body。URL 或 Method 为空抛 [`RequestException`](RequestException.md)。该异常在加入 / 构建阶段抛出，不会进入 `$exceptions`。

`getCurlHeaders()` / `analysisHeaders()` 逻辑与 [Client](Client.md) 相同。

---

## 示例

```php
use Fize\Http\ClientMulti;
use Fize\Http\Request;

$multi = new ClientMulti();
$multi->addRequest(new Request('GET', 'https://example.com/a'), 'a');
$multi->addRequest(new Request('GET', 'https://example.com/b'), 'b');

$exceptions = [];
$responses = $multi->send($exceptions);

foreach ($responses as $key => $response) {
    if ($response === null) {
        echo $key, ': ', $exceptions[$key]->getMessage(), PHP_EOL;
        continue;
    }
    echo $key, ': ', $response->getStatusCode(), PHP_EOL;
}

// 一次性传入
$responses = (new ClientMulti())->sendRequests([
    'x' => new Request('GET', 'https://example.com/x'),
    'y' => new Request('GET', 'https://example.com/y'),
], $exceptions);
```

[返回目录](index.md)
