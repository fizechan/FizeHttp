# UploadedFileFactory

PSR-17 上传文件工厂，并提供 `$_FILES` 与 [`UploadedFile`](UploadedFile.md) 之间的双向转换。

- 源码：`src/UploadedFileFactory.php`
- 全名：`Fize\Http\UploadedFileFactory`
- 实现：`Psr\Http\Message\UploadedFileFactoryInterface`

---

## 概述

PHP 的 `$_FILES` 在多文件、嵌套字段时结构与 PSR-7 的「树状 UploadedFile」不同。本类负责：

- 从流创建单个上传对象（PSR-17）
- 从 `$_FILES` 单项 / 整树创建对象
- 在测试中把规范数组或 `UploadedFile` 写回 `$_FILES`

识别「上传项」的条件：数组同时含 `tmp_name`、`name`、`size`、`type`、`error`。可选键 `for_test` 为真时调用 `UploadedFile::forTest()`。

---

## PSR-17

```php
public function createUploadedFile(
    StreamInterface $stream,
    ?int $size = null,
    int $error = UPLOAD_ERR_OK,
    ?string $clientFilename = null,
    ?string $clientMediaType = null
): UploadedFileInterface
```

直接 `new UploadedFile(...)`。注意：[`UploadedFile`](UploadedFile.md) 的 `$size` 类型为 `int`，传入 `null` 会依赖 PHP 的类型强制。

---

## 从 $_FILES 构建

### createUploadedFileFromSpec

```php
public function createUploadedFileFromSpec(array $file): UploadedFileInterface
```

单项必须是完整上传项，否则 `InvalidArgumentException`。

### createUploadedFilesFromSpec

```php
public function createUploadedFilesFromSpec(array $files): array
```

递归处理：

- 自身已是上传项且 `tmp_name` 为数组 → 拆成多个 `UploadedFile` 的列表
- 自身已是上传项且 `tmp_name` 为标量 → 单元素列表
- 键值为上传项 → 保留键名
- 否则继续递归

[ServerRequestFactory::createServerRequestFromGlobals()](ServerRequestFactory.md) 使用本方法解析 `$_FILES`。

### createUploadedFileFromGlobals

```php
public function createUploadedFileFromGlobals($name): UploadedFileInterface
```

`$name` 为字段名字符串，或从顶层开始的键路径数组。字段不存在、或多文件（`tmp_name` 为数组）时抛 `InvalidArgumentException`。

### createUploadedFilesFromGlobals

```php
public function createUploadedFilesFromGlobals($name = null): array
```

`$name` 为 `null` 时处理整个 `$_FILES`。

---

## 测试辅助

### setGlobals

```php
public static function setGlobals(array $files, bool $format = false): void
```

`$format = true` 时，把「字段 => 上传项列表」转成 PHP 多文件的并列数组结构。

### setGlobalsByUploadedFiles

```php
public static function setGlobalsByUploadedFiles(array $uploadedFiles): void
```

把 `UploadedFile` 树写成 `$_FILES`。单文件、多文件、嵌套字段均可。测试模式会带上 `for_test`。

---

## 示例

```php
use Fize\Http\StreamFactory;
use Fize\Http\UploadedFileFactory;

$factory = new UploadedFileFactory();

$stream = (new StreamFactory())->createStream('file-content');
$file = $factory->createUploadedFile($stream, 12, UPLOAD_ERR_OK, 'a.txt', 'text/plain');

// 从 $_FILES 单项
$one = $factory->createUploadedFileFromSpec($_FILES['avatar']);

// 测试：写入超全局后再构建 ServerRequest
UploadedFileFactory::setGlobals([
    'avatar' => [
        'tmp_name' => '/tmp/phpXXXX',
        'name' => 'a.txt',
        'size' => 12,
        'type' => 'text/plain',
        'error' => UPLOAD_ERR_OK,
        'for_test' => true,
    ],
]);
```

[返回目录](index.md)
