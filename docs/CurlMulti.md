# CurlMulti

`curl_multi_*` 批处理句柄封装。兼容 PHP 7 的 `resource` 与 PHP 8 的 `CurlMultiHandle`。

- 源码：`src/CurlMulti.php`
- 全名：`Fize\Http\CurlMulti`
- 标记：`@deprecated`
- 被 [ClientMulti](ClientMulti.md) 使用

---

## 构造

```php
public function __construct()
```

内部 `curl_multi_init()`。析构时若句柄有效则 `close()`。

---

## 方法

| 方法 | 对应函数 | 说明 |
|------|----------|------|
| `addHandle($ch): int` | `curl_multi_add_handle` | `0` 表示成功 |
| `removeHandle($ch)` | `curl_multi_remove_handle` | 成功返回 `CURLM_*`，失败 `false` |
| `exec(?int &$still_running = null): int` | `curl_multi_exec` | 返回批处理状态码 |
| `select(float $timeout = 1.0): int` | `curl_multi_select` | 等待活动连接，失败 `-1` |
| `infoRead(?int &$msgs_in_queue = null)` | `curl_multi_info_read` | 失败 `false` |
| `getcontent($ch): string` | `curl_multi_getcontent` | **静态**；需 `CURLOPT_RETURNTRANSFER` |
| `setopt(int $option, $value): bool` | `curl_multi_setopt` | `CURLMOPT_*` |
| `strerror(int $errornum): string` | `curl_multi_strerror` | **静态** |
| `close()` | `curl_multi_close` | |

典型驱动循环（[ClientMulti::send()](ClientMulti.md) 即此模式）：

```php
use Fize\Http\Curl;
use Fize\Http\CurlMulti;

$cm = new CurlMulti();
$c1 = new Curl('https://example.com/a', [CURLOPT_RETURNTRANSFER => true]);
$c2 = new Curl('https://example.com/b', [CURLOPT_RETURNTRANSFER => true]);
$cm->addHandle($c1->getHandle());
$cm->addHandle($c2->getHandle());

$still = 0;
do {
    $mrc = $cm->exec($still);
} while ($mrc == CURLM_CALL_MULTI_PERFORM);

while ($still && $mrc == CURLM_OK) {
    if ($cm->select() != -1) {
        do {
            $mrc = $cm->exec($still);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    }
}

echo CurlMulti::getcontent($c1->getHandle());
```

[返回目录](index.md)
