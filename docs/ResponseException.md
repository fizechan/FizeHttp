# ResponseException

携带 [`Response`](Response.md) 的运行时异常。**不是** PSR-18 接口实现。

- 源码：`src/ResponseException.php`
- 全名：`Fize\Http\ResponseException`
- 继承：`RuntimeException`

---

## 概述

本库的客户端**不会自动抛出**此类。用于应用层在拿到响应后，按状态码或业务码主动中断，同时保留完整响应（状态、报头、body）。

构造参数顺序与常见 SPL 异常不同：`$response` 在最前，`$message` / `$code` 在后。

---

## 构造

```php
public function __construct(
    Response $response,
    ?Throwable $previous = null,
    string $message = '',
    int $code = 0
)
```

注意第一个参数类型是具体类 `Fize\Http\Response`，不是 `ResponseInterface`。

### getResponse

```php
public function getResponse(): Response
```

---

## 示例

```php
use Fize\Http\ClientOnce;
use Fize\Http\ResponseException;

$response = ClientOnce::get('https://example.com/api');
if ($response->getStatusCode() >= 400) {
    throw new ResponseException(
        $response,
        null,
        'HTTP error',
        $response->getStatusCode()
    );
}

try {
    // ...
} catch (ResponseException $e) {
    $res = $e->getResponse();
    echo $res->getStatusCode();
    echo (string)$res->getBody();
}
```

[返回目录](index.md)
