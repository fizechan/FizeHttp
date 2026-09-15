# RequestFactory

PSR-17 请求工厂。

- 源码：`src/RequestFactory.php`
- 全名：`Fize\Http\RequestFactory`
- 实现：`Psr\Http\Message\RequestFactoryInterface`
- 产物：[Request](Request.md)

---

## 方法

```php
public function createRequest(string $method, $uri): RequestInterface
```

`$uri` 为 `string` 或 `UriInterface`。等价于 `new Request($method, $uri)`（body 为空、无额外报头、协议 `1.1`）。

```php
use Fize\Http\RequestFactory;

$factory = new RequestFactory();
$request = $factory->createRequest('GET', 'https://example.com/users');
```

需要 body 或自定义报头时，直接 `new Request(...)`，或对工厂结果再 `withBody()` / `withHeader()`。

[返回目录](index.md)
