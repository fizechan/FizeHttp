# UriFactory

PSR-17 URI 工厂。

- 源码：`src/UriFactory.php`
- 全名：`Fize\Http\UriFactory`
- 实现：`Psr\Http\Message\UriFactoryInterface`
- 产物：[Uri](Uri.md)

---

## 方法

```php
public function createUri(string $uri = ''): UriInterface
```

等价于 `new Uri($uri)`。空字符串得到各组件皆空的 URI。非法 URL 的行为见 [Uri](Uri.md) 构造函数。

```php
use Fize\Http\UriFactory;

$factory = new UriFactory();
$uri = $factory->createUri('https://example.com/path?q=1#top');
```

[返回目录](index.md)
