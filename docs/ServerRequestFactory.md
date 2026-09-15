# ServerRequestFactory

PSR-17 服务端请求工厂，并提供从超全局变量构建 / 回写的辅助方法。

- 源码：`src/ServerRequestFactory.php`
- 全名：`Fize\Http\ServerRequestFactory`
- 实现：`Psr\Http\Message\ServerRequestFactoryInterface`
- 产物：[ServerRequest](ServerRequest.md)

---

## 方法

### createServerRequest

```php
public function createServerRequest(
    string $method,
    $uri,                         // string|UriInterface
    array $serverParams = []
): ServerRequestInterface
```

body 与 headers 均为空。等价于 `new ServerRequest($method, $uri, null, [], $serverParams)`。

### createServerRequestFromGlobals

```php
public function createServerRequestFromGlobals(): ServerRequestInterface
```

从当前 SAPI 环境构建：

| 来源 | 写入 |
|------|------|
| `$_SERVER['REQUEST_METHOD']` | 方法，默认 `GET` |
| `getallheaders()`（若存在） | 报头 |
| `$_SERVER` 推导 | URI（见下） |
| `php://input` | `CachingStream(LazyOpenStream(...))` |
| `SERVER_PROTOCOL` | 去掉 `HTTP/` 前缀，默认 `1.1` |
| `$_COOKIE` / `$_GET` / `$_POST` | Cookie / query / parsedBody |
| `$_FILES` | [`UploadedFileFactory::createUploadedFilesFromSpec`](UploadedFileFactory.md) |

URI 推导顺序：

1. scheme：`HTTPS` 非空且不为 `off` 则为 `https`，否则 `http`
2. host：`HTTP_HOST`（可带端口）→ `SERVER_NAME` → `SERVER_ADDR`
3. 端口：`HTTP_HOST` 中的端口，否则 `SERVER_PORT`
4. path / query：`REQUEST_URI`，若无 query 再读 `QUERY_STRING`

### setGlobals

```php
public static function setGlobals(ServerRequestInterface $request): void
```

把 `ServerRequest` 写回 `$_SERVER` / `$_COOKIE` / `$_GET` / `$_POST` / `$_REQUEST`，并用 [`UploadedFileFactory::setGlobalsByUploadedFiles`](UploadedFileFactory.md) 写 `$_FILES`。**主要用于单元测试**。

部分 `$_SERVER` 字段是近似填充（例如 `REMOTE_ADDR` 使用 URI host，`SCRIPT_FILENAME` 为当前工厂文件路径），不要当作真实 CGI 环境。

---

## 示例

```php
use Fize\Http\ServerRequest;
use Fize\Http\ServerRequestFactory;

$factory = new ServerRequestFactory();

// 手工
$sr = $factory->createServerRequest('GET', 'https://example.com/api', [
    'REMOTE_ADDR' => '127.0.0.1',
]);

// 当前请求
$sr = $factory->createServerRequestFromGlobals();

// 测试：先构造再写回超全局
$sr = (new ServerRequest('POST', 'https://example.com/login'))
    ->withParsedBody(['user' => 'a'])
    ->withQueryParams(['from' => 'web']);
ServerRequestFactory::setGlobals($sr);
```

[返回目录](index.md)
