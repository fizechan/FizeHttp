# ClientException

PSR-18 客户端异常基类。

- 源码：`src/ClientException.php`
- 全名：`Fize\Http\ClientException`
- 继承：`RuntimeException`
- 实现：`Psr\Http\Client\ClientExceptionInterface`
- 子类：[RequestException](RequestException.md)、[NetworkException](NetworkException.md)

---

## 概述

空类，无自定义属性或方法。表示「发送 PSR-7 请求时发生的、且不属于更具体子类的错误」。

[Client](Client.md) / [ClientMulti](ClientMulti.md) 在 cURL 报错、且错误不是「无法解析主机 / 无法连接」时抛出本类。消息与错误码来自 `curl_error()` / `curl_errno()`。

```php
use Fize\Http\Client;
use Fize\Http\ClientException;
use Fize\Http\Request;

try {
    (new Client())->sendRequest(new Request('GET', 'https://example.com'));
} catch (ClientException $e) {
    echo $e->getMessage(), ' #', $e->getCode();
}
```

捕获 `ClientException` 可同时接到 `RequestException` 与 `NetworkException`。

[返回目录](index.md)
