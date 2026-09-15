# Response

HTTP 响应，实现 PSR-7 `Psr\Http\Message\ResponseInterface`。

- 源码：`src/Response.php`
- 全名：`Fize\Http\Response`
- 继承：[Message](Message.md)
- 工厂：[ResponseFactory](ResponseFactory.md)

---

## 概述

`Response` 在 [Message](Message.md) 之上增加状态码与原因短语。内置 100–511 及 `600 Unparseable Response Headers` 的短语表；未指定短语时按状态码查表。

状态码必须是 **100–599** 的整数，否则抛 `InvalidArgumentException`。

---

## 构造

```php
public function __construct(
    $body = null,              // string|null|resource|StreamInterface
    int $status = 200,
    array $headers = [],
    string $protocol_version = '1.1',
    ?string $reason = null
)
```

| 参数 | 默认 | 说明 |
|------|------|------|
| `$body` | `null` | 处理方式同 [Request](Request.md) |
| `$status` | `200` | 100–599 |
| `$headers` | `[]` | 报头 |
| `$protocol_version` | `'1.1'` | 协议版本 |
| `$reason` | `null` | 为空时使用内置短语 |

---

## 方法

### getStatusCode

```php
public function getStatusCode(): int
```

### withStatus

```php
public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
```

`$reasonPhrase` 为空时查内置表；表中没有则保留空字符串。状态码非法时抛 `InvalidArgumentException`。

### getReasonPhrase

```php
public function getReasonPhrase(): string
```

---

## 内置短语要点

| 码 | 本库短语 | 备注 |
|----|----------|------|
| 200 | OK | |
| 302 | **Move Temporarily** | RFC 7231 推荐 `Found` |
| 418 | I'm a teapot | |
| 449 | Retry With | 非标准（IIS） |
| 509 | Bandwidth Limit Exceeded | 非标准 |
| 600 | Unparseable Response Headers | 在短语表中，但构造 / `withStatus` 不允许 `>= 600` |

完整表见 `src/Response.php` 中的 `$phrases`。

---

## 示例

```php
use Fize\Http\Response;

$response = new Response('这是响应内容', 502);
$response->getStatusCode();     // 502
$response->getReasonPhrase();   // Bad Gateway

$response = $response->withStatus(404);
$response->getReasonPhrase();   // Not Found

$response = $response->withStatus(299, 'Custom');
$response->getReasonPhrase();   // Custom
```

[返回目录](index.md)
