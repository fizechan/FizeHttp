# NetworkException

网络层失败：无法解析主机或无法建立连接。

- 源码：`src/NetworkException.php`
- 全名：`Fize\Http\NetworkException`
- 继承：[ClientException](ClientException.md)
- 实现：`Psr\Http\Client\NetworkExceptionInterface`

---

## 何时抛出

[Client](Client.md) 在 `curl_errno()` 为以下值时抛出：

- `CURLE_COULDNT_RESOLVE_HOST`
- `CURLE_COULDNT_CONNECT`

[ClientMulti](ClientMulti.md) 遇到同样错误时**不抛出**，而是把本异常放入 `$exceptions[$key]`，对应响应为 `null`。

消息与错误码来自 cURL。

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

### getRequest

```php
public function getRequest(): RequestInterface
```

返回关联请求。注释说明：因中间件等原因，该对象可能与最初发送的不一致。

```php
use Fize\Http\Client;
use Fize\Http\NetworkException;
use Fize\Http\Request;

try {
    (new Client(null, 3, 1))->sendRequest(
        new Request('GET', 'https://no-such-host.invalid/')
    );
} catch (NetworkException $e) {
    echo $e->getMessage();
    echo (string)$e->getRequest()->getUri();
}
```

[返回目录](index.md)
