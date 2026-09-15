# ServerRequest

服务端 HTTP 请求，实现 PSR-7 `Psr\Http\Message\ServerRequestInterface`。

- 源码：`src/ServerRequest.php`
- 全名：`Fize\Http\ServerRequest`
- 继承：[Request](Request.md)
- 工厂：[ServerRequestFactory](ServerRequestFactory.md)

---

## 概述

在 [Request](Request.md) 之上增加服务端才有的数据：`$_SERVER` 风格参数、Cookie、查询参数、上传文件树、解析后的消息体，以及中间件可写入的 attributes。

从超全局变量构建请使用 [`ServerRequestFactory::createServerRequestFromGlobals()`](ServerRequestFactory.md)。

---

## 构造

```php
public function __construct(
    string $method,
    $uri,                                    // string|UriInterface
    $body = null,                            // string|null|resource|StreamInterface
    array $headers = [],
    array $serverParams = [],
    string $protocol_version = '1.1'
)
```

`$serverParams` 通常传入 `$_SERVER`。其余参数含义与 [Request](Request.md) 相同。

---

## 属性

| 属性 | 默认 | 说明 |
|------|------|------|
| `$serverParams` | 构造传入 | 服务器参数，无 `with*`（PSR-7 约定不可变） |
| `$cookieParams` | `[]` | Cookie |
| `$queryParams` | `[]` | 查询参数 |
| `$uploadedFiles` | `[]` | 上传文件树（[`UploadedFile`](UploadedFile.md)） |
| `$parsedBody` | `null` | 解析后的 body |
| `$attributes` | `[]` | 派生属性 |

---

## 方法

| 方法 | 签名 | 说明 |
|------|------|------|
| `getServerParams` | `(): array` | 服务器参数 |
| `getCookieParams` | `(): array` | Cookie |
| `withCookieParams` | `(array $cookies): ServerRequestInterface` | 替换 Cookie |
| `getQueryParams` | `(): array` | 查询参数 |
| `withQueryParams` | `(array $query): ServerRequestInterface` | 替换查询参数 |
| `getUploadedFiles` | `(): array` | 规范化上传文件树 |
| `withUploadedFiles` | `(array $uploadedFiles): ServerRequestInterface` | 替换上传文件 |
| `getParsedBody` | `()` | `array\|object\|null` |
| `withParsedBody` | `($data): ServerRequestInterface` | 设置解析后的 body |
| `getAttributes` | `(): array` | 全部派生属性 |
| `getAttribute` | `(string $name, $default = null)` | 单个属性，不存在返回 `$default` |
| `withAttribute` | `(string $name, $value): ServerRequestInterface` | 设置属性 |
| `withoutAttribute` | `(string $name): ServerRequestInterface` | 移除属性；不存在则返回 `$this` |

`withParsedBody()` 按 PSR-7 约定应传入数组、对象或 `null`。当 Content-Type 为 `application/x-www-form-urlencoded` 或 `multipart/form-data` 且方法为 POST 时，数据应对应 `$_POST`。

---

## 示例

```php
use Fize\Http\ServerRequest;

$sr = new ServerRequest(
    'GET',
    'https://example.com/users',
    null,
    [],
    ['REMOTE_ADDR' => '127.0.0.1']
);

$sr = $sr
    ->withQueryParams(['page' => '1'])
    ->withCookieParams(['sid' => 'abc'])
    ->withAttribute('userId', 42);

$sr->getServerParams()['REMOTE_ADDR']; // 127.0.0.1
$sr->getAttribute('userId');           // 42
$sr->getAttribute('missing', 'n/a');   // n/a
```

[返回目录](index.md)
