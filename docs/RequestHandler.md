# RequestHandler

PSR-15 请求处理器范式实现，实现 `Psr\Http\Server\RequestHandlerInterface`。

- 源码：`src/RequestHandler.php`
- 全名：`Fize\Http\RequestHandler`
- 协作：[Middleware](Middleware.md)

---

## 概述

源码注释写明：**只是范式，无实际业务逻辑**。`handle()` 忽略请求内容，返回默认 [`Response`](Response.md)（状态 200、空 body）。

应用层应继承本类并在 `handle()` 中完成路由、控制器调用等。

---

## 方法

```php
public function handle(ServerRequestInterface $request): ResponseInterface
```

默认：`return new Response();`

---

## 示例

```php
use Fize\Http\RequestHandler;
use Fize\Http\Response;
use Fize\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppHandler extends RequestHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        if ($path === '/health') {
            return new Response('ok', 200);
        }
        return new Response('Not Found', 404);
    }
}

$sr = new ServerRequest('GET', 'https://example.com/health');
echo (string)(new AppHandler())->handle($sr)->getBody(); // ok
```

[返回目录](index.md)
