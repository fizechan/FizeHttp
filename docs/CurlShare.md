# CurlShare

`curl_share_*` 共享句柄封装，用于在多个 easy 句柄间共享 Cookie、DNS 等数据。

- 源码：`src/CurlShare.php`
- 全名：`Fize\Http\CurlShare`
- 标记：`@deprecated`

本库的 [Client](Client.md) / [ClientMulti](ClientMulti.md) **并未使用**本类。Cookie 持久化走的是按 host 命名的文件，而不是 share handle。

---

## 构造

```php
public function __construct(array $options = [])
```

`curl_share_init()` 后按数组逐项 `setopt`。析构时 `close()`。

---

## 方法

| 方法 | 对应函数 | 说明 |
|------|----------|------|
| `close()` | `curl_share_close` | |
| `errno(): int` | `curl_share_errno` | |
| `setopt(int $option, $value): bool` | `curl_share_setopt` | 如 `CURLSHOPT_SHARE` |
| `strerror(int $errornum): string` | `curl_share_strerror` | **静态** |
| `getHandle()` | — | 原始共享句柄 |

---

## 示例

```php
use Fize\Http\Curl;
use Fize\Http\CurlShare;

$share = new CurlShare([
    CURLSHOPT_SHARE => CURL_LOCK_DATA_COOKIE,
]);

$a = new Curl('https://example.com/login');
$a->setopt(CURLOPT_SHARE, $share->getHandle());

$b = new Curl('https://example.com/profile');
$b->setopt(CURLOPT_SHARE, $share->getHandle());
```

[返回目录](index.md)
