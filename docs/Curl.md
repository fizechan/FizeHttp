# Curl

单句柄 cURL 的面向对象封装。兼容 PHP 7 的 `resource` 与 PHP 8 的 `CurlHandle`。

- 源码：`src/Curl.php`
- 全名：`Fize\Http\Curl`
- 标记：`@deprecated`（文档写明 PHP 8 后原生已支持，建议后续版本改用原生类型）
- 被 [Client](Client.md)、[ClientMulti](ClientMulti.md) 使用

---

## 概述

方法一一对应 `curl_*` 函数，便于在 PHP 7 下统一句柄生命周期。析构时若句柄仍有效会 `close()`。

---

## 构造

```php
public function __construct(?string $url = null, array $options = [])
```

`curl_init($url)`，非空 URL 再设 `CURLOPT_URL`，非空 `$options` 则 `setoptArray`。

---

## 方法

| 方法 | 对应函数 | 说明 |
|------|----------|------|
| `close()` | `curl_close` | 关闭会话 |
| `copyHandle()` | `curl_copy_handle` | 复制句柄与选项 |
| `errno(): int` | `curl_errno` | 最后错误号 |
| `error(): string` | `curl_error` | 最后错误信息 |
| `escape(string $str)` | `curl_escape` | URL 编码，失败 `false` |
| `exec()` | `curl_exec` | 执行，失败 `false` |
| `fileCreate(string $filename, string $mimetype, string $postname): CURLFile` | `curl_file_create` | **静态**，创建上传对象 |
| `getinfo(?int $opt = null)` | `curl_getinfo` | `$opt` 为空返回全部信息数组 |
| `pause(int $bitmask): int` | `curl_pause` | `CURLPAUSE_*` |
| `reset()` | `curl_reset` | 重置选项 |
| `setoptArray(array $options): bool` | `curl_setopt_array` | |
| `setopt(int $option, $value): bool` | `curl_setopt` | |
| `strerror(int $errornum): string` | `curl_strerror` | **静态** |
| `unescape(string $str): string` | `curl_unescape` | |
| `version(): array` | `curl_version` | **静态** |
| `getHandle()` | — | 原始句柄 |

`isCurlHandle()` 为私有静态方法，同时识别 `resource(curl)` 与类名 `CurlHandle`。

---

## 示例

```php
use Fize\Http\Curl;

$curl = new Curl('https://example.com', [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
]);
$content = $curl->exec();
$info = $curl->getinfo();
if ($curl->errno()) {
    throw new RuntimeException($curl->error(), $curl->errno());
}

$file = Curl::fileCreate('/tmp/a.jpg', 'image/jpeg', 'photo');
```

一般业务应使用 [Client](Client.md) 系列，而不是直接操作本类。

[返回目录](index.md)
