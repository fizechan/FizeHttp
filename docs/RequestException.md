# RequestException

请求本身无效，尚未进入有效网络传输。

- 源码：`src/RequestException.php`
- 全名：`Fize\Http\RequestException`
- 继承：[ClientException](ClientException.md)
- 实现：`Psr\Http\Client\RequestExceptionInterface`

---

## 何时抛出

[Client::sendRequest()](Client.md) 与 [ClientMulti::buildHandle()](ClientMulti.md) 在以下情况抛出：

- 请求 URI 转字符串后为空
- HTTP 方法为空

---

## 构造

```php
public function __construct(
    RequestInterface $request,
    string $message = '',
    int $code = 0,
    ?Throwable $previous = null
)
```

库内抛出时通常只传入 `$request`，消息与错误码为空。

### getRequest

```php
public function getRequest(): RequestInterface
```

返回关联请求。注释说明：该对象可能与调用方最初传入的并不完全一致（例如经过中间处理）。

```php
use Fize\Http\Client;
use Fize\Http\Request;
use Fize\Http\RequestException;
use Fize\Http\Uri;

try {
    (new Client())->sendRequest(new Request('GET', new Uri()));
} catch (RequestException $e) {
    $e->getRequest()->getMethod(); // GET
}
```

[返回目录](index.md)
