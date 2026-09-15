# Middleware

PSR-15 中间件范式实现，实现 `Psr\Http\Server\MiddlewareInterface`。

- 源码：`src/Middleware.php`
- 全名：`Fize\Http\Middleware`
- 协作：[RequestHandler](RequestHandler.md)、[ServerRequest](ServerRequest.md)

---

## 概述

源码注释写明：**只是范式，无实际业务逻辑**。`process()` 直接把请求交给 `$handler->handle()`。

实际项目应继承本类（或直接实现 `MiddlewareInterface`），在调用 `$handler->handle()` 前后读写请求 / 响应。本库不提供中间件管道（dispatcher / stack）。

---

## 方法

```php
public function process(
    ServerRequestInterface $request,
    RequestHandlerInterface $handler
): ResponseInterface
```

默认实现：

```php
return $handler->handle($request);
```

---

## 示例

```php
use Fize\Http\Middleware;
use Fize\Http\RequestHandler;
use Fize\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NotFoundMiddleware extends Middleware
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);
        return $response->withStatus(404);
    }
}

$sreq = new ServerRequest('GET', 'https://www.example.com/');
$response = (new NotFoundMiddleware())->process($sreq, new RequestHandler());
// $response->getStatusCode() === 404
```

典型洋葱模型需要自行实现「当前中间件 + 剩余链」的 `RequestHandlerInterface`，本库未内置。

[返回目录](index.md)
