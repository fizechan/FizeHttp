# Request

客户端 HTTP 请求，实现 PSR-7 `Psr\Http\Message\RequestInterface`。

- 源码：`src/Request.php`
- 全名：`Fize\Http\Request`
- 继承：[Message](Message.md)
- 子类：[ServerRequest](ServerRequest.md)
- 工厂：[RequestFactory](RequestFactory.md)

---

## 概述

`Request` 描述一次即将发出的 HTTP 请求：方法、URI、请求目标、报头与消息体。构造时若未显式提供 `Host` 头，会按 RFC 7230 §5.4 从 URI 推导，并把 `Host` 放到报头首位。

方法名会自动转为大写。字符串 URI 会包装为 [`Uri`](Uri.md)。

---

## 构造

```php
public function __construct(
    string $method,
    $uri,                                    // string|UriInterface
    $body = null,                            // string|null|resource|StreamInterface
    array $headers = [],
    string $protocol_version = '1.1'
)
```

| 参数 | 默认 | 说明 |
|------|------|------|
| `$method` | — | HTTP 方法，空或非字符串抛 `InvalidArgumentException` |
| `$uri` | — | 字符串则 `new Uri($uri)` |
| `$body` | `null` | 字符串 → `StreamFactory::createStream`；resource → `createStreamFromResource`；已是 `StreamInterface` 则直接使用 |
| `$headers` | `[]` | 报头数组 |
| `$protocol_version` | `'1.1'` | 协议版本 |

---

## 属性

| 属性 | 可见性 | 类型 | 说明 |
|------|--------|------|------|
| `$requestTarget` | protected | `string\|null` | 自定义请求目标 |
| `$uri` | protected | `UriInterface` | 请求 URI |
| `$method` | protected | `string` | HTTP 方法（大写） |

另继承 [Message](Message.md) 的协议版本、报头与流。

---

## 方法

### getRequestTarget

```php
public function getRequestTarget(): string
```

优先返回 `withRequestTarget()` 设置的值。否则：

- 路径为空时用 `/`
- 有 query 时拼接 `?query`

### withRequestTarget

```php
public function withRequestTarget(string $requestTarget): RequestInterface
```

设置 origin-form 请求目标。含空白字符时抛 `InvalidArgumentException`。

### getMethod / withMethod

```php
public function getMethod(): string
public function withMethod(string $method): RequestInterface
```

`withMethod()` 会校验非空字符串并转为大写。

### getUri / withUri

```php
public function getUri(): UriInterface
public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
```

`$preserveHost = true` 且当前已有 `Host` 头时，保留原 Host（适用于代理）。否则按新 URI 更新 Host。

---

## 受保护方法

| 方法 | 说明 |
|------|------|
| `assertMethod($method)` | 方法必须是非空字符串 |
| `updateHostFromUri()` | 从 URI 写入 Host（含非默认端口），并置于报头首位 |

---

## 使用注意

- URI 路径与 query 会经 [`Uri`](Uri.md) 做 percent-encoding，因此 `getRequestTarget()` 看到的可能是编码后的字符串。
- 继承 [Message](Message.md) 的全部公共方法。

---

## 示例

```php
use Fize\Http\Request;
use Fize\Http\Uri;

$request = new Request(
    'POST',
    'https://www.example.com/test?kw=中文',
    'name=fize',
    ['Content-Type' => 'application/x-www-form-urlencoded']
);

$request->getMethod();           // POST
$request->getRequestTarget();    // /test?kw=%E4%B8%AD%E6%96%87
$request->getHeaderLine('Host'); // www.example.com

$request = $request
    ->withMethod('GET')
    ->withUri(new Uri('https://www.qq.com'));
```

[返回目录](index.md)
