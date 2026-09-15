# ResponseFactory

PSR-17 响应工厂。

- 源码：`src/ResponseFactory.php`
- 全名：`Fize\Http\ResponseFactory`
- 实现：`Psr\Http\Message\ResponseFactoryInterface`
- 产物：[Response](Response.md)

---

## 方法

```php
public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
```

等价于 `new Response(null, $code, [], '1.1', $reasonPhrase)`。状态码约束见 [Response](Response.md)。

```php
use Fize\Http\ResponseFactory;

$factory = new ResponseFactory();
$ok = $factory->createResponse();
$notFound = $factory->createResponse(404);
$custom = $factory->createResponse(299, 'Custom');
```

[返回目录](index.md)
