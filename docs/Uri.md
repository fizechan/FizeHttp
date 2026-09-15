# Uri

RFC 3986 URI 对象，实现 PSR-7 `Psr\Http\Message\UriInterface`，并提供规范化、相对解析、查询参数操作等扩展。

- 源码：`src/Uri.php`
- 全名：`Fize\Http\Uri`
- 工厂：[UriFactory](UriFactory.md)

---

## 概述

`Uri` 将 URL 拆成 scheme、userInfo、host、port、path、query、fragment。`with*` 在新值与当前值相同时直接返回 `$this`。默认端口（如 http/80、https/443）存储为 `null`，不出现在字符串形式中。

http / https 且 host 为空时，会自动填入常量 `HTTP_DEFAULT_HOST`（`localhost`）。

---

## 构造

```php
public function __construct(string $url = '')
```

非空 `$url` 先经 `normalizeUrl()` 处理，再 `parse_url()`。解析失败抛 `InvalidArgumentException`。

若传入的已是 `filter_var(..., FILTER_VALIDATE_URL)` 认可的标准 URL，则原样使用。非标准 URL 会尝试补协议、编码路径等，**此路径含调试输出，生产环境应传入合法 URL**。

---

## 规范化标志（位掩码）

| 常量 | 值 | 说明 |
|------|-----|------|
| `PRESERVING_NORMALIZATIONS` | 63 | 默认安全组合（1+2+4+8+16+32） |
| `CAPITALIZE_PERCENT_ENCODING` | 1 | `%xx` 大写 |
| `DECODE_UNRESERVED_CHARACTERS` | 2 | 解码未保留字符 |
| `CONVERT_EMPTY_PATH` | 4 | http(s) 空 path → `/` |
| `REMOVE_DEFAULT_HOST` | 8 | `file://localhost` 去掉 localhost |
| `REMOVE_DEFAULT_PORT` | 16 | 去掉默认端口 |
| `REMOVE_DOT_SEGMENTS` | 32 | 去掉 `.` / `..` |
| `REMOVE_DUPLICATE_SLASHES` | 64 | 合并连续 `/`（语义上可能改变路径） |
| `SORT_QUERY_PARAMETERS` | 128 | 按字母排序 query（**可能改变语义**） |
| `HTTP_DEFAULT_HOST` | `'localhost'` | http(s) 无 host 时的默认值 |

默认端口表：http 80、https 443、ftp 21、gopher 70、nntp/news 119、telnet/tn3270 23、imap 143、pop 110、ldap 389。

---

## PSR-7 方法

| 方法 | 签名 | 说明 |
|------|------|------|
| `__toString` | `(): string` | 组装完整 URI |
| `getScheme` | `(): string` | 小写 scheme |
| `getAuthority` | `(): string` | `[userinfo@]host[:port]` |
| `getUserInfo` | `(): string` | `user` 或 `user:password` |
| `getHost` | `(): string` | 小写 host |
| `getPort` | `(): ?int` | 默认端口为 `null` |
| `getPath` | `(): string` | 路径 |
| `getQuery` | `(): string` | 查询串（不含 `?`） |
| `getFragment` | `(): string` | 片段（不含 `#`） |
| `withScheme` | `(string $scheme): UriInterface` | 小写化；去掉默认端口 |
| `withUserInfo` | `(string $user, ?string $password = null): UriInterface` | percent-encode |
| `withHost` | `(string $host): UriInterface` | 小写 |
| `withPort` | `(?int $port): UriInterface` | `null` 表示使用默认端口；范围 0–65535 |
| `withPath` | `(string $path): UriInterface` | 过滤非法字符 |
| `withQuery` | `(string $query): UriInterface` | 不含前导 `?` |
| `withFragment` | `(string $fragment): UriInterface` | 不含前导 `#` |

非法端口或违反 RFC 3986 状态（例如有 authority 但 path 不以 `/` 开头）抛 `InvalidArgumentException`。

---

## 扩展方法

### composeComponents

```php
public static function composeComponents(
    string $scheme,
    string $authority,
    string $path,
    string $query,
    string $fragment
): string
```

按 RFC 3986 §5.3 组装。`file` scheme 即使 authority 为空也会写出 `//`。

### 引用类型判断

| 方法 | 含义 |
|------|------|
| `isAbsolute(): bool` | 有 scheme |
| `isNetworkPathReference(): bool` | `//authority` 形式 |
| `isAbsolutePathReference(): bool` | 以 `/` 开头的相对引用 |
| `isRelativePathReference(): bool` | 相对路径引用 |
| `isDefaultPort(): bool` | 端口为 `null` 或等于该 scheme 默认值 |

### isSameDocumentReference

```php
public static function isSameDocumentReference(UriInterface $uri, ?UriInterface $base = null): bool
```

提供 `$base` 时先 `resolve` 再比较 scheme/authority/path/query。未提供 `$base` 时，仅当 `$uri` 各组件皆空视为同文档引用。

### removeDotSegments

```php
public static function removeDotSegments(string $path): string
```

RFC 3986 §5.2.4 路径标准化。

### 查询参数

```php
public function withoutQueryParam(string $key): UriInterface
public function withQueryParam(string $key, ?string $value): UriInterface
public function withQueryParams(array $keyValueArray): UriInterface
```

先移除同名键，再追加。`$value === null` 时只写键名、不写 `=`。键和值中的 `=` / `&` 会被编码为 `%3D` / `%26`。

### fromParts

```php
public static function fromParts(array $parts): Uri
```

从 `parse_url()` 风格数组构建。

### normalize

```php
public function normalize(int $flags = self::PRESERVING_NORMALIZATIONS): UriInterface
```

按位标志做规范化。`SORT_QUERY_PARAMETERS` 会改变参数顺序，默认不包含。

### resolve / relativize

```php
public static function resolve(UriInterface $base, UriInterface $rel): UriInterface
public static function relativize(UriInterface $base, UriInterface $target): UriInterface
```

`resolve` 将相对 URI 解析为绝对 URI。`relativize` 计算从 `$base` 到 `$target` 的相对引用。

---

## 使用注意

1. 构造函数对非标准 URL 的修复逻辑（`normalizeUrl()`）会 `print_r` 查询参数值，并可能改写 query / fragment。传入已校验的标准 URL 可避开该路径。
2. 有 authority 时 path 必须为空或以 `/` 开头；无 authority 时 path 不得以 `//` 开头。
3. 相对 URI 的第一段不能含 `:`，以免被当成 scheme。

---

## 示例

```php
use Fize\Http\Uri;

$uri = new Uri('https://www.baidu.com/test?wd=搜索一下#t123');
$uri->getScheme();    // https
$uri->getHost();      // www.baidu.com
$uri->getQuery();     // 已编码的查询串

$uri = $uri->withQueryParam('page', '2')->withoutQueryParam('wd');

$composed = Uri::composeComponents(
    'https',
    'www.baidu.com:88',
    '/test1/test2',
    'wd=' . urlencode('中国'),
    'top'
);

$rel = Uri::relativize(
    new Uri('https://example.com/a'),
    new Uri('https://example.com/a/b/c')
);

$abs = Uri::resolve(
    new Uri('https://example.com/dir/page'),
    new Uri('../other')
);
```

[返回目录](index.md)
