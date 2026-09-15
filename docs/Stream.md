# Stream

HTTP 命名空间下的数据流，实现 PSR-7 `Psr\Http\Message\StreamInterface`。

- 源码：`src/Stream.php`
- 全名：`Fize\Http\Stream`
- 继承：`Fize\Stream\Stream`（包 `fize/stream`）
- 工厂：[StreamFactory](StreamFactory.md)

---

## 概述

本类**没有新增方法或属性**，只是把 `fize/stream` 的实现暴露到 `Fize\Http` 命名空间，便于与本库其他类型一起使用。行为、选项与异常均由父类决定。

析构时会 `close()`。

---

## 构造

```php
public function __construct($stream = null, array $options = [])
```

| 参数 | 说明 |
|------|------|
| `$stream` | PHP 流资源；也可稍后 `open()` |
| `$options['size']` | 预先指定字节数 |
| `$options['metadata']` | 自定义元数据，优先于 `stream_get_meta_data()` |

```php
use Fize\Http\Stream;

$st = new Stream(fopen(__FILE__, 'r'));
echo $st->getContents();
```

---

## PSR-7 方法

| 方法 | 签名 | 说明 | 异常 |
|------|------|------|------|
| `__toString` | `(): string` | `seek(0)` 后读完全部；失败返回 `''` | — |
| `close` | `(): void` | 关闭并释放底层资源 | — |
| `detach` | `()` | 分离资源，之后流不可用 | — |
| `getSize` | `(): ?int` | 字节数，未知为 `null` | — |
| `tell` | `(): int` | 当前指针 | `RuntimeException` |
| `eof` | `(): bool` | 是否到末尾 | `RuntimeException` |
| `isSeekable` | `(): bool` | 是否可随机访问 | — |
| `seek` | `(int $offset, int $whence = SEEK_SET): void` | 定位 | `RuntimeException` |
| `rewind` | `(): void` | 回到开头 | 同 `seek` |
| `isWritable` | `(): bool` | 是否可写 | — |
| `write` | `(string $string): int` | 写入，返回字节数 | `RuntimeException` |
| `isReadable` | `(): bool` | 是否可读 | — |
| `read` | `(int $length): string` | 读取指定长度 | `RuntimeException` |
| `getContents` | `(): string` | 读取剩余内容 | `RuntimeException` |
| `getMetadata` | `(?string $key = null)` | 元数据；`$key` 为 `null` 返回全部 | — |

读写能力由流 `mode` 决定（父类用正则匹配可读 / 可写模式）。`detach()` 后几乎所有读写操作都会抛 `RuntimeException: Stream is detached`。

---

## 扩展方法（继承自 fize/stream）

### open

```php
public function open(
    string $file,
    ?string $mode = null,
    bool $use_include_path = false,
    $context = null
)
```

用 `fopen` 打开文件。当前已有流且未关闭时抛 `RuntimeException`。本地文件且模式会创建文件时，自动 `mkdir` 父目录。

### copyToString / copyToStream

```php
public static function copyToString(StreamInterface $stream, int $maxLen = -1): string
public static function copyToStream(
    StreamInterface $source,
    StreamInterface $dest,
    int $maxLen = -1
)
```

流复制工具。`$maxLen = -1` 表示不限制。`UploadedFile::moveTo()` 在源为流时会调用 `copyToStream()`。

### createTemp

```php
public static function createTemp(array $options = []): StreamInterface
```

创建 `php://temp` 流并返回 `new static(...)`，因此得到的是 `Fize\Http\Stream`。

---

## 使用注意

- `__toString()` 吞掉异常并返回空字符串，调试时优先用 `getContents()`。
- `write()` 会把缓存的 `size` 置为 `null`，下次 `getSize()` 重新统计。
- 更完整的流协议（`LazyOpenStream`、`CachingStream`、`PumpStream`）在 `fize/stream` 中，本库的 [ServerRequestFactory](ServerRequestFactory.md) 等会直接使用它们。

[返回目录](index.md)
