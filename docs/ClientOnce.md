# ClientOnce

静态、一次性 HTTP 动词客户端。每次调用新建 [ClientSimple](ClientSimple.md)（`$host` 为空）。

- 源码：`src/ClientOnce.php`
- 全名：`Fize\Http\ClientOnce`
- 实例对照：[ClientEasy](ClientEasy.md)

---

## 概述

适合脚本或测试里「打一次就走」的请求。不持有状态，因此也无法在多次调用间复用同一个 Cookie 文件，除非每次传入相同的 `$cookie_dir`。

动词集合与 [ClientEasy](ClientEasy.md) 一致。`head()` 同样设置 `CURLOPT_NOBODY`。

---

## 方法

公共方法全部为 `static`，返回 `ResponseInterface`。

无 body 的动词：

```php
public static function get(
    string $url,
    array $headers = [],
    array $opts = [],
    ?string $cookie_dir = null,
    int $time_out = 30,
    int $retries = 1
): ResponseInterface
```

`options` / `head` / `delete` / `patch` / `trace` / `move` / `copy` / `link` / `unlink` / `wrapped` 的参数表相同。

带 body：

```php
public static function post(
    string $url,
    $body,                    // string|array
    array $headers = [],
    array $opts = [],
    ?string $cookie_dir = null,
    int $time_out = 30,
    int $retries = 1
): ResponseInterface
```

`put()` 相同，`$body` 默认 `''`。

内部：

```php
protected static function sendRequest(
    string $method,
    string $url,
    $body = null,
    array $headers = [],
    array $opts = [],
    ?string $cookie_dir = null,
    int $time_out = 30,
    int $retries = 1
): ResponseInterface
```

创建 `new ClientSimple('', $cookie_dir, $time_out, $retries, $opts)` 后转发。

与 `ClientEasy` 一样，`patch()` 不传 body。

---

## 示例

```php
use Fize\Http\ClientOnce;

$response = ClientOnce::get('https://www.baidu.com');
echo (string)$response->getBody();

$response = ClientOnce::post(
    'http://api.fanyi.baidu.com/api/trans/vip/translate',
    http_build_query(['q' => '你好', 'from' => 'zh', 'to' => 'en']),
    ['Content-Type' => 'application/x-www-form-urlencoded']
);

$response = ClientOnce::head('https://www.baidu.com');
```

[返回目录](index.md)
