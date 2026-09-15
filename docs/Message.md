# Message

抽象基类，实现 PSR-7 `Psr\Http\Message\MessageInterface`。封装 HTTP **协议版本、报头、消息体** 的通用逻辑，不可直接实例化。

- 源码：`src/Message.php`
- 全名：`Fize\Http\Message`
- 子类：[Request](Request.md)、[Response](Response.md)

---

## 概述

`Message` 是本库所有 HTTP 消息的公共父类。报头查找**不区分大小写**；`with*` 方法均 `clone` 后修改，符合 PSR-7 不可变约定。

`getBody()` 在尚未设置流时会懒创建 `php://temp` 空流，因此调用方总能拿到 `StreamInterface`。

---

## 属性

| 属性 | 可见性 | 类型 | 说明 |
|------|--------|------|------|
| `$protocolVersion` | protected | `string` | HTTP 协议版本，如 `1.1` |
| `$headers` | protected | `array` | 报头，格式为 `['Name' => ['val1', 'val2']]` |
| `$stream` | protected | [`Stream`](Stream.md) | 消息体流 |

---

## 方法

### getProtocolVersion

```php
public function getProtocolVersion(): string
```

返回当前协议版本字符串。

### withProtocolVersion

```php
public function withProtocolVersion(string $version): MessageInterface
```

返回设置了新协议版本的实例。

```php
$message = $message->withProtocolVersion('2.0');
```

### getHeaders

```php
public function getHeaders(): array
```

返回全部报头。键为报头名，值为字符串数组。

### hasHeader

```php
public function hasHeader(string $name): bool
```

是否存在指定报头。**键名不区分大小写**。

### getHeader

```php
public function getHeader(string $name): array
```

按名称取报头值数组。不存在时返回 `[]`。键名不区分大小写。

### getHeaderLine

```php
public function getHeaderLine(string $name): string
```

将 `getHeader()` 的结果用逗号拼接。不存在时返回空字符串。

```php
// Accept: text/html, application/xml
$line = $request->getHeaderLine('Accept');
```

### withHeader

```php
public function withHeader(string $name, $value): MessageInterface
```

**替换**指定报头。`$value` 可为 `string` 或 `string[]`。若已存在同名（大小写不敏感）报头，先删除旧键再以传入的 `$name` 写入。

抛出 `InvalidArgumentException`：

- 名称为空或非字符串
- 值为空数组
- 值既非标量也非 `null`

### withAddedHeader

```php
public function withAddedHeader(string $name, $value): MessageInterface
```

**追加**报头。已存在同名报头时，用 `array_flip` 合并并去重（后加入的同值项会覆盖原索引位置）。校验规则同 `withHeader()`。

### withoutHeader

```php
public function withoutHeader(string $name): MessageInterface
```

移除指定报头。不存在时仍返回克隆实例。

### getBody

```php
public function getBody(): StreamInterface
```

返回消息体流。若内部 `$stream` 尚未设置，打开 `php://temp` 并包装为 [`Stream`](Stream.md)。

注意：懒创建会写回当前实例的 `$stream`，因此连续调用 `getBody()` 得到的是同一流对象。

### withBody

```php
public function withBody(StreamInterface $body): MessageInterface
```

返回替换了消息体的新实例。

---

## 受保护方法

| 方法 | 说明 |
|------|------|
| `getRealHeaderName(string $name): ?string` | 大小写不敏感查找真实键名，不存在返回 `null` |
| `assertHeaderName($name)` | 校验报头名 |
| `normalizeHeaderValue($value): array` | 规范为字符串数组 |
| `setHeaders(array $headers)` | 批量设置；同名报头合并。供子类构造使用 |

`setHeaders()` 会把 PHP 把数字键转成 `int` 的情况再转回字符串，因此名为 `123` 的报头是合法的。

---

## 使用注意

- `withHeader()` 写入时保留调用方给出的大小写（例如把 `accept` 写成 `Accept`）。
- `withAddedHeader()` 的去重基于值字符串，不是简单 `array_merge`。
- 报头值会去掉首尾空格与制表符，避免破坏 HTTP 协议。

---

## 示例

```php
use Fize\Http\Request;

$request = new Request('GET', 'https://www.example.com');
$request = $request
    ->withHeader('Accept', ['text/html', 'application/xml'])
    ->withAddedHeader('accept', 'text/html') // 与已有值去重
    ->withProtocolVersion('1.1');

$request->hasHeader('ACCEPT');          // true
$request->getHeader('Accept');          // ['text/html', 'application/xml']
(string)$request->getBody();            // 懒创建后的空字符串

$request = $request->withoutHeader('accept');
```

[返回目录](index.md)
