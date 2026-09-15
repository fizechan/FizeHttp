# UploadedFile

PSR-7 上传文件，实现 `Psr\Http\Message\UploadedFileInterface`。

- 源码：`src/UploadedFile.php`
- 全名：`Fize\Http\UploadedFile`
- 工厂：[UploadedFileFactory](UploadedFileFactory.md)

---

## 概述

同时支持真实 PHP 上传、流，以及测试模式（`copy` 而不删除源文件）。错误码必须是 PHP 的 `UPLOAD_ERR_*` 之一。

传入文件系统路径且未指定客户端文件名 / MIME 时：

- 文件名 = `basename($path)`
- MIME = `Fize\IO\File::getMime()`（建议启用 `ext-fileinfo`）

仅当错误码为 `UPLOAD_ERR_OK` 时才会绑定底层文件或流。

---

## 构造

```php
public function __construct(
    $streamOrFile,                 // StreamInterface|string|resource
    int $size,
    int $errorStatus,
    ?string $clientFilename = null,
    ?string $clientMediaType = null
)
```

`$streamOrFile` 非法或 `$errorStatus` 不是合法上传错误码时抛 `InvalidArgumentException`。

```php
use Fize\Http\UploadedFile;

$upf = new UploadedFile(__FILE__, filesize(__FILE__), UPLOAD_ERR_OK);
```

---

## 方法

### getStream

```php
public function getStream(): StreamInterface
```

已是流则直接返回；文件路径则用 `Fize\Stream\Protocol\LazyOpenStream` 延迟打开。

上传失败或已经 `moveTo()` 后调用，抛 `RuntimeException`。

### moveTo

```php
public function moveTo(string $targetPath): void
```

| 条件 | 行为 |
|------|------|
| 空路径 | `InvalidArgumentException` |
| 测试模式 + 文件 | `copy()`，不删原文件 |
| CLI + 文件 | `rename()` |
| Web SAPI + 文件 | `move_uploaded_file()` |
| 源为流 | `Stream::copyToStream()` 写到目标 |

目标已存在同名文件，或父目录创建失败，抛 `RuntimeException`。成功后 `isMoved()` 为 `true`，不可再次移动或取流。

`checkPath()` 会按 `0777` 递归创建目标目录。

### 元数据

| 方法 | 返回 | 说明 |
|------|------|------|
| `getSize()` | `?int` | 构造时传入的字节数 |
| `getError()` | `int` | `UPLOAD_ERR_*` |
| `getClientFilename()` | `?string` | 客户端文件名，**不可信** |
| `getClientMediaType()` | `?string` | 客户端 MIME，**不可信** |
| `getTmpName()` | `string` | 底层流 metadata 的 `uri` |
| `isMoved()` | `bool` | 是否已移动 |

### 测试模式

```php
public function forTest(bool $forTest = true): void
public function isForTest(): bool
```

测试模式下 `moveTo()` 仅复制。单元测试或从非 `$_FILES` 路径构造时使用。

```php
$upf = new UploadedFile(__FILE__, filesize(__FILE__), UPLOAD_ERR_OK);
$upf->forTest(true);
$upf->moveTo('/tmp/uploaded.txt');
```

---

## 使用注意

- `getClientFilename()` / `getClientMediaType()` 来自客户端或本地推断，不能用于安全决策。
- 非 `UPLOAD_ERR_OK` 时无法 `getStream()` / `moveTo()`。
- Web 环境下对非真实上传文件调用 `move_uploaded_file()` 会失败，这类场景应 `forTest(true)` 或使用流。

[返回目录](index.md)
