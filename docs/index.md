# FizeHttp 参考手册

FizeHttp（Composer 包名 `fize/http`）是遵循 **PSR-7、PSR-15、PSR-17、PSR-18** 的 PHP HTTP 底层库。它同时提供：

- 符合规范的消息、URI、流、上传文件与工厂类
- 基于 cURL 的 HTTP 客户端（含易用封装与并发客户端）
- PSR-15 中间件 / 请求处理器的范式实现
- 面向 PHP 7 的 cURL 面向对象封装（PHP 8+ 建议改用原生句柄）

源码命名空间为 `Fize\Http`，类文件位于 `src/`。

| 项目 | 说明 |
|------|------|
| 包名 | `fize/http` |
| 命名空间 | `Fize\Http` |
| 许可 | MIT |
| PHP | `>= 7.2.0` |
| 仓库 | [github.com/fizechan/FizeHttp](https://github.com/fizechan/FizeHttp) |

---

## 安装

```bash
composer require fize/http
```

### 运行要求

| 依赖 | 用途 |
|------|------|
| `ext-curl` | 客户端底层传输（必需） |
| `fize/io` | Cookie 文件创建、上传文件 MIME 检测 |
| `fize/stream` | `Stream` / `StreamFactory` 的实际实现 |
| `psr/http-message` | PSR-7 消息接口 |
| `psr/http-factory` | PSR-17 工厂接口 |
| `psr/http-client` | PSR-18 客户端接口 |
| `psr/http-server-handler` | PSR-15 请求处理器 |
| `psr/http-server-middleware` | PSR-15 中间件 |

建议安装 `ext-fileinfo`，以便 `UploadedFile` 更准确地推断客户端 MIME。

---

## 架构

```
应用层    ClientEasy / ClientOnce          HTTP 动词便捷 API
封装层    ClientSimple                     host 拼接、body 序列化、文件上传
核心层    Client (PSR-18) / ClientMulti    单请求 / 并发请求
传输层    Curl / CurlMulti / CurlShare     ext-curl 面向对象封装
消息层    Message → Request / Response     PSR-7 消息
          ServerRequest / Uri / Stream / UploadedFile
工厂层    *Factory                         PSR-17
服务端    Middleware / RequestHandler      PSR-15 范式
异常层    ClientException 及其子类         PSR-18 + ResponseException
```

### 客户端如何选型

| 场景 | 选用 | 说明 |
|------|------|------|
| 需要 PSR-18 互操作 | [`Client`](Client.md) | 接收 `RequestInterface`，返回 `ResponseInterface` |
| 固定主机的 REST 调用 | [`ClientEasy`](ClientEasy.md) | 实例方法 `get()` / `post()` 等，可复用 Cookie 与超时 |
| 一次性脚本 | [`ClientOnce`](ClientOnce.md) | 静态方法，每次新建客户端，传入完整 URL |
| 需要自己控制 body 序列化 | [`ClientSimple`](ClientSimple.md) | `sendRequest($method, $uri, $body, $headers)` |
| 批量并发 | [`ClientMulti`](ClientMulti.md) | `curl_multi_*`，失败项为 `null` 并收集异常 |

### 消息继承关系

```
Message（抽象，PSR-7 MessageInterface）
├── Request（PSR-7 RequestInterface）
│   └── ServerRequest（PSR-7 ServerRequestInterface）
└── Response（PSR-7 ResponseInterface）
```

所有 `with*` 方法均 `clone` 后修改，原对象不变。

### 异常继承关系

```
RuntimeException
├── ClientException（PSR-18 ClientExceptionInterface）
│   ├── RequestException（请求无效：空 URL / 空方法）
│   └── NetworkException（无法解析主机 / 无法连接）
└── ResponseException（非 PSR，携带 Response，供应用层自行抛出）
```

---

## PSR 对照

| 规范 | 接口 | 实现类 |
|------|------|--------|
| PSR-7 | `MessageInterface` | [`Message`](Message.md)（抽象） |
| PSR-7 | `RequestInterface` | [`Request`](Request.md) |
| PSR-7 | `ServerRequestInterface` | [`ServerRequest`](ServerRequest.md) |
| PSR-7 | `ResponseInterface` | [`Response`](Response.md) |
| PSR-7 | `UriInterface` | [`Uri`](Uri.md) |
| PSR-7 | `StreamInterface` | [`Stream`](Stream.md) |
| PSR-7 | `UploadedFileInterface` | [`UploadedFile`](UploadedFile.md) |
| PSR-17 | `RequestFactoryInterface` | [`RequestFactory`](RequestFactory.md) |
| PSR-17 | `ResponseFactoryInterface` | [`ResponseFactory`](ResponseFactory.md) |
| PSR-17 | `ServerRequestFactoryInterface` | [`ServerRequestFactory`](ServerRequestFactory.md) |
| PSR-17 | `StreamFactoryInterface` | [`StreamFactory`](StreamFactory.md) |
| PSR-17 | `UriFactoryInterface` | [`UriFactory`](UriFactory.md) |
| PSR-17 | `UploadedFileFactoryInterface` | [`UploadedFileFactory`](UploadedFileFactory.md) |
| PSR-18 | `ClientInterface` | [`Client`](Client.md) |
| PSR-18 | `ClientExceptionInterface` | [`ClientException`](ClientException.md) |
| PSR-18 | `RequestExceptionInterface` | [`RequestException`](RequestException.md) |
| PSR-18 | `NetworkExceptionInterface` | [`NetworkException`](NetworkException.md) |
| PSR-15 | `MiddlewareInterface` | [`Middleware`](Middleware.md) |
| PSR-15 | `RequestHandlerInterface` | [`RequestHandler`](RequestHandler.md) |

非 PSR 扩展类：[`ClientSimple`](ClientSimple.md)、[`ClientEasy`](ClientEasy.md)、[`ClientOnce`](ClientOnce.md)、[`ClientMulti`](ClientMulti.md)、[`Curl`](Curl.md)、[`CurlMulti`](CurlMulti.md)、[`CurlShare`](CurlShare.md)、[`ResponseException`](ResponseException.md)。

---

## 快速开始

### 一次性 GET / POST

```php
use Fize\Http\ClientOnce;

$response = ClientOnce::get('https://example.com');
echo (string)$response->getBody();

$response = ClientOnce::post(
    'https://example.com/api',
    ['name' => 'fize'],
    ['Content-Type' => 'application/x-www-form-urlencoded']
);
```

### 固定主机的易用客户端

```php
use Fize\Http\ClientEasy;

$client = new ClientEasy('https://api.example.com');
$response = $client->get('/users/1');
$response = $client->post('/users', ['name' => 'fize']);
```

### PSR-18 标准客户端

```php
use Fize\Http\Client;
use Fize\Http\Request;

$client = new Client();
$request = new Request(
    'POST',
    'https://api.example.com/translate',
    http_build_query(['q' => '你好', 'from' => 'zh', 'to' => 'en']),
    ['Content-Type' => 'application/x-www-form-urlencoded']
);
$response = $client->sendRequest($request);
echo $response->getStatusCode();
echo (string)$response->getBody();
```

### 从超全局变量构建服务端请求

```php
use Fize\Http\ServerRequestFactory;

$factory = new ServerRequestFactory();
$request = $factory->createServerRequestFromGlobals();
```

---

## 章节目录

### 消息与 URI

- [Message](Message.md) — HTTP 消息基类（协议版本、报头、消息体）
- [Request](Request.md) — 客户端请求
- [ServerRequest](ServerRequest.md) — 服务端请求
- [Response](Response.md) — 响应
- [Uri](Uri.md) — URI（含规范化、相对解析等扩展）
- [Stream](Stream.md) — 数据流
- [UploadedFile](UploadedFile.md) — 上传文件

### 工厂（PSR-17）

- [RequestFactory](RequestFactory.md)
- [ResponseFactory](ResponseFactory.md)
- [ServerRequestFactory](ServerRequestFactory.md)
- [StreamFactory](StreamFactory.md)
- [UriFactory](UriFactory.md)
- [UploadedFileFactory](UploadedFileFactory.md)

### 客户端

- [Client](Client.md) — PSR-18 核心客户端
- [ClientSimple](ClientSimple.md) — 简易封装
- [ClientEasy](ClientEasy.md) — 实例化动词 API
- [ClientOnce](ClientOnce.md) — 静态单次请求
- [ClientMulti](ClientMulti.md) — 并发客户端

### 传输层

- [Curl](Curl.md)
- [CurlMulti](CurlMulti.md)
- [CurlShare](CurlShare.md)

### 服务端（PSR-15）

- [Middleware](Middleware.md)
- [RequestHandler](RequestHandler.md)

### 异常

- [ClientException](ClientException.md)
- [RequestException](RequestException.md)
- [NetworkException](NetworkException.md)
- [ResponseException](ResponseException.md)

---

## 使用注意（摘要）

1. **消息不可变**：`with*` 返回新实例，必须接住返回值。
2. **`Client` 的 `$retries` 会在实例生命周期内递减**，不是每次请求都重置。
3. **`ClientMulti` 的 `$retries` 已声明但未实现**，且不跟随重定向。
4. 重定向跟随仅 [`Client`](Client.md) 支持，且依赖手动设置 `CURLOPT_FOLLOWLOCATION`，由本库解析 `Location` 后递归发送，不是 cURL 原生跟随。
5. 响应头解析时同名头后者覆盖前者，解析出的值为字符串（不是多值数组）。
6. [`Uri::normalizeUrl()`](Uri.md) 在修复非标准 URL 时含调试输出，生产环境应尽量传入合法 URL。
7. [`Curl`](Curl.md) / [`CurlMulti`](CurlMulti.md) / [`CurlShare`](CurlShare.md) 已标记 `@deprecated`，PHP 8+ 可直接使用原生 `CurlHandle` 等类型。
8. [`StreamFactory::creatStreamFromCallable()`](StreamFactory.md) 方法名拼写为 `creat`（继承自 `fize/stream`）。

各章节末尾均可回到本页：[返回目录](index.md)。
