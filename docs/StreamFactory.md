# StreamFactory

PSR-17 流工厂。本类无新增代码，继承 `Fize\Stream\StreamFactory`。

- 源码：`src/StreamFactory.php`
- 全名：`Fize\Http\StreamFactory`
- 实现：`Psr\Http\Message\StreamFactoryInterface`
- 相关：[Stream](Stream.md)

---

## 概述

用于从字符串、文件、资源、迭代器、对象或回调创建 `StreamInterface`。[Request](Request.md) / [Response](Response.md) 构造函数在收到字符串或 resource body 时会内部使用本工厂。

---

## PSR-17 方法

### createStream

```php
public function createStream(string $content = ''): StreamInterface
```

从字符串创建（底层一般为 `php://temp`）。

### createStreamFromFile

```php
public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
```

打开文件为流。

```php
use Fize\Http\StreamFactory;

$factory = new StreamFactory();
$st = $factory->createStreamFromFile(__FILE__);
```

### createStreamFromResource

```php
public function createStreamFromResource($resource): StreamInterface
```

包装已有 PHP 流资源。

---

## 扩展方法（继承自 fize/stream）

### createStreamFromIterator

```php
public function createStreamFromIterator(Iterator $iterator, array $options = []): StreamInterface
```

把迭代器包装为按需读取的 `PumpStream`。

### createStreamFromObject

```php
public function createStreamFromObject($object): StreamInterface
```

- 已是 `StreamInterface`：原样返回
- 实现了 `__toString`：转为字符串流
- 其他：抛 `InvalidArgumentException`

### creatStreamFromCallable

```php
public function creatStreamFromCallable(callable $function, array $options = []): StreamInterface
```

从回调创建 `PumpStream`。**方法名少一个 e**（`creat` 而非 `create`），这是 `fize/stream` 的既有拼写，调用时必须使用该拼写。

---

## 示例

```php
use Fize\Http\StreamFactory;

$factory = new StreamFactory();

$fromString = $factory->createStream('hello');
$fromFile = $factory->createStreamFromFile('/tmp/data.txt', 'r');
$fromResource = $factory->createStreamFromResource(fopen('php://temp', 'r+'));
```

[返回目录](index.md)
