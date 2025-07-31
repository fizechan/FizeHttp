<?php

namespace Fize\Http;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

/**
 * URI 对象
 */
class Uri implements UriInterface
{

    /**
     * 默认的规范化，只包括保留语义的规范化
     */
    public const PRESERVING_NORMALIZATIONS = 63;

    /**
     * 一个百分比编码的三连音中的所有字母(例如“%3A”)是不区分大小写的，应该大写。
     *
     * 例如: http://example.org/a%c2%b1b → http://example.org/a%C2%B1b
     */
    public const CAPITALIZE_PERCENT_ENCODING = 1;

    /**
     * 解码未保留字符的百分比编码字节。
     *
     * 例如: http://example.org/%7Eusern%61me/ → http://example.org/~username/
     */
    public const DECODE_UNRESERVED_CHARACTERS = 2;

    /**
     * 将http和https uri的空路径转换为“/”。
     *
     * 例如: http://example.org → http://example.org/
     */
    public const CONVERT_EMPTY_PATH = 4;

    /**
     * 从URI中删除给定URI方案的默认主机。
     *
     * 例如: file://localhost/myfile → file:///myfile
     */
    public const REMOVE_DEFAULT_HOST = 8;

    /**
     * 从URI中删除给定URI方案的默认端口。
     *
     * 例如: http://example.org:80/ → http://example.org/
     */
    public const REMOVE_DEFAULT_PORT = 16;

    /**
     * 移除不必要的相对路径
     *
     * 例如: http://example.org/../a/b/../c/./d.html → http://example.org/a/c/d.html
     */
    public const REMOVE_DOT_SEGMENTS = 32;

    /**
     * 包含两个或多个相邻斜杠的路径被转换为一个斜杠。
     *
     * 例如: http://example.org//foo///bar.html → http://example.org/foo/bar.html
     */
    public const REMOVE_DUPLICATE_SLASHES = 64;

    /**
     * 将查询参数及其值按字母顺序排序。
     * URI中参数的顺序可能很重要(这不是由标准定义的)。
     * 所以这种标准化是不安全的，可能会改变URI的语义。
     *
     * 例如: ?lang=en&article=fred → ?article=fred&lang=en
     */
    public const SORT_QUERY_PARAMETERS = 128;

    /**
     * 默认主机名
     */
    public const HTTP_DEFAULT_HOST = 'localhost';

    /**
     * @var array 默认端口
     */
    private static $defaultPorts = [
        'http'   => 80,
        'https'  => 443,
        'ftp'    => 21,
        'gopher' => 70,
        'nntp'   => 119,
        'news'   => 119,
        'telnet' => 23,
        'tn3270' => 23,
        'imap'   => 143,
        'pop'    => 110,
        'ldap'   => 389,
    ];

    /**
     * @var string 无需转义的字符正则
     */
    private static $charUnreserved = 'a-zA-Z0-9_\-\.~';

    /**
     * @var string 其他无需转义字段
     */
    private static $charSubDelims = '!\$&\'\(\)\*\+,;=';

    /**
     * @var array 参数要转义的字符
     */
    private static $replaceQuery = ['=' => '%3D', '&' => '%26'];

    /**
     * @var string 协议
     */
    private $scheme = '';

    /**
     * @var string 用户
     */
    private $userInfo = '';

    /**
     * @var string 主机
     */
    private $host = '';

    /**
     * @var int|null 端口
     */
    private $port;

    /**
     * @var string 路径
     */
    private $path = '';

    /**
     * @var string 参数
     */
    private $query = '';

    /**
     * @var string 锚点
     */
    private $fragment = '';

    /**
     * 构造
     * @param string $url 待解析URL
     * @throws InvalidArgumentException
     */
    public function __construct(string $url = '')
    {
        if ($url != '') {
            $url = $this->normalizeUrl($url);
            $parts = parse_url($url);
            if ($parts === false) {
                throw new InvalidArgumentException("Unable to parse URL: {$url}");
            }
            $this->applyParts($parts);
        }
    }

    /**
     * 转字符串
     * @return string
     */
    public function __toString(): string
    {
        return self::composeComponents(
            $this->scheme,
            $this->getAuthority(),
            $this->path,
            $this->query,
            $this->fragment
        );
    }

    /**
     * 组装部件，返回完整URI
     * @param string $scheme    协议
     * @param string $authority 认证
     * @param string $path      路径
     * @param string $query     参数
     * @param string $fragment  锚点
     * @return string
     * @link https://tools.ietf.org/html/rfc3986#section-5.3
     */
    public static function composeComponents(string $scheme, string $authority, string $path, string $query, string $fragment): string
    {
        $uri = '';

        if ($scheme != '') {
            $uri .= $scheme . ':';
        }

        if ($authority != '' || $scheme === 'file') {
            $uri .= '//' . $authority;
        }

        $uri .= $path;

        if ($query != '') {
            $uri .= '?' . $query;
        }

        if ($fragment != '') {
            $uri .= '#' . $fragment;
        }

        return $uri;
    }

    /**
     * 返回协议信息
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * 返回验证信息
     * @return string
     */
    public function getAuthority(): string
    {
        $authority = $this->host;
        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }

        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * 返回用户信息
     * @return string
     */
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    /**
     * 返回主机信息
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * 返回端口信息
     * @return int|null
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * 返回路径信息
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * 返回参数信息
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * 返回锚点信息
     * @return string
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * 返回指定协议信息的实例
     * @param string $scheme 协议
     * @return static
     */
    public function withScheme(string $scheme): UriInterface
    {
        $scheme = $this->filterScheme($scheme);

        if ($this->scheme === $scheme) {
            return $this;
        }

        $new = clone $this;
        $new->scheme = $scheme;
        $new->removeDefaultPort();
        $new->validateState();

        return $new;
    }

    /**
     * 返回指定用户信息的实例
     * @param string      $user     用户名
     * @param string|null $password 密码
     * @return static
     */
    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $info = $this->filterUserInfoComponent($user);
        if ($password !== null) {
            $info .= ':' . $this->filterUserInfoComponent($password);
        }

        if ($this->userInfo === $info) {
            return $this;
        }

        $new = clone $this;
        $new->userInfo = $info;
        $new->validateState();

        return $new;
    }

    /**
     * 返回指定主机信息的实例
     * @param string $host 主机
     * @return static
     */
    public function withHost(string $host): UriInterface
    {
        $host = $this->filterHost($host);

        if ($this->host === $host) {
            return $this;
        }

        $new = clone $this;
        $new->host = $host;
        $new->validateState();

        return $new;
    }

    /**
     * 返回指定端口信息的实例
     * @param int|null $port 端口，null表示不指定使用默认端口
     * @return static
     */
    public function withPort(?int $port): UriInterface
    {
        $port = $this->filterPort($port);

        if ($this->port === $port) {
            return $this;
        }

        $new = clone $this;
        $new->port = $port;
        $new->removeDefaultPort();
        $new->validateState();

        return $new;
    }

    /**
     * 返回指定路径信息的实例
     * @param string $path 路径
     * @return static
     */
    public function withPath(string $path): UriInterface
    {
        $path = $this->filterPath($path);

        if ($this->path === $path) {
            return $this;
        }

        $new = clone $this;
        $new->path = $path;
        $new->validateState();

        return $new;
    }

    /**
     * 返回指定参数信息的实例
     * @param string $query 参数
     * @return static
     */
    public function withQuery(string $query): UriInterface
    {
        $query = $this->filterQueryAndFragment($query);

        if ($this->query === $query) {
            return $this;
        }

        $new = clone $this;
        $new->query = $query;

        return $new;
    }

    /**
     * 返回指定锚点信息的实例
     * @param string $fragment 锚点
     * @return static
     */
    public function withFragment(string $fragment): UriInterface
    {
        $fragment = $this->filterQueryAndFragment($fragment);

        if ($this->fragment === $fragment) {
            return $this;
        }

        $new = clone $this;
        $new->fragment = $fragment;

        return $new;
    }

    /**
     * 判断URI是否完整，即其以协议开始
     * @return bool
     * @link https://tools.ietf.org/html/rfc3986#section-4
     */
    public function isAbsolute(): bool
    {
        return $this->getScheme() !== '';
    }

    /**
     * 判断URI是否为网络路径引用
     *
     * 以两个斜杠字符开头的相对引用称为网络路径引用
     * @return bool
     * @link https://tools.ietf.org/html/rfc3986#section-4.2
     */
    public function isNetworkPathReference(): bool
    {
        return $this->getScheme() === '' && $this->getAuthority() !== '';
    }

    /**
     * 判断URI是否为绝对路径
     * @return bool
     * @link https://tools.ietf.org/html/rfc3986#section-4.3
     */
    public function isAbsolutePathReference(): bool
    {
        return $this->getScheme() === '' && $this->getAuthority() === '' && isset($this->getPath()[0]) && $this->getPath()[0] === '/';
    }

    /**
     * 判断URI是否为相对路径
     * @return bool
     * @link https://tools.ietf.org/html/rfc3986#section-4.2
     */
    public function isRelativePathReference(): bool
    {
        return $this->getScheme() === '' && $this->getAuthority() === '' && (!isset($this->getPath()[0]) || $this->getPath()[0] !== '/');
    }

    /**
     * 判断两个URI对象是否指向同一个资源
     *
     * 参数 `$base`:
     *   未提供参数则认为是和默认初始目录进行比较
     * @param UriInterface      $uri  要检验的URI对象
     * @param UriInterface|null $base 用于检验的URI对象
     * @return bool
     * @link https://tools.ietf.org/html/rfc3986#section-4.4
     */
    public static function isSameDocumentReference(UriInterface $uri, UriInterface $base = null): bool
    {
        if ($base !== null) {
            $uri = self::resolve($base, $uri);
            return
                ($uri->getScheme() === $base->getScheme()) &&
                ($uri->getAuthority() === $base->getAuthority()) &&
                ($uri->getPath() === $base->getPath()) &&
                ($uri->getQuery() === $base->getQuery());
        }

        return $uri->getScheme() === '' && $uri->getAuthority() === '' && $uri->getPath() === '' && $uri->getQuery() === '';
    }

    /**
     * 判断是否使用默认端口
     * @return bool
     */
    public function isDefaultPort(): bool
    {
        $port = $this->getPort();
        if ($port === null) {
            return true;
        }

        $scheme = $this->getScheme();
        if (isset(self::$defaultPorts[$scheme]) && $port === self::$defaultPorts[$scheme]) {
            return true;
        }

        return false;
    }

    /**
     * 从路径中删除点段并返回新路径，即标准化路径。
     * @param string $path 路径
     * @return string
     * @link http://tools.ietf.org/html/rfc3986#section-5.2.4
     */
    public static function removeDotSegments(string $path): string
    {
        if ($path === '' || $path === '/') {
            return $path;
        }

        $results = [];
        $segments = explode('/', $path);
        $segment = null;
        foreach ($segments as $segment) {
            if ($segment === '..') {
                array_pop($results);
            } elseif ($segment !== '.') {
                $results[] = $segment;
            }
        }

        $new_path = implode('/', $results);
        if ($path[0] === '/' && (!isset($new_path[0]) || $new_path[0] !== '/')) {
            // Re-add the leading slash if necessary for cases like "/.."
            $new_path = '/' . $new_path;
        } elseif ($new_path !== '' && ($segment === '.' || $segment === '..')) {
            // Add the trailing slash if necessary
            // If newPath is not empty, then $segment must be set and is the last segment from the foreach
            $new_path .= '/';
        }
        return $new_path;
    }

    /**
     * 移除指定参数
     * @param string $key 键名
     * @return UriInterface
     */
    public function withoutQueryParam(string $key): UriInterface
    {
        $result = $this->getFilteredQueryString([$key]);
        return $this->withQuery(implode('&', $result));
    }

    /**
     * 添加指定参数
     * @param string      $key   键名
     * @param string|null $value 键值
     * @return UriInterface
     */
    public function withQueryParam(string $key, ?string $value): UriInterface
    {
        $result = $this->getFilteredQueryString([$key]);
        $result[] = self::generateQueryString($key, $value);
        return $this->withQuery(implode('&', $result));
    }

    /**
     * 添加多个参数
     * @param array $keyValueArray 参数键值对
     * @return UriInterface
     */
    public function withQueryParams(array $keyValueArray): UriInterface
    {
        $result = $this->getFilteredQueryString(array_keys($keyValueArray));

        foreach ($keyValueArray as $key => $value) {
            $result[] = self::generateQueryString($key, $value);
        }

        return $this->withQuery(implode('&', $result));
    }

    /**
     * 获取参数列表
     * @param array $keys 键名在该数组内的将不返回
     * @return array 数组项格式为x=y
     */
    private function getFilteredQueryString(array $keys): array
    {
        $current = $this->getQuery();

        if ($current === '') {
            return [];
        }

        $decoded_keys = array_map('rawurldecode', $keys);
        return array_filter(
            explode('&', $current),
            function ($part) use ($decoded_keys) {
                return !in_array(rawurldecode(explode('=', $part)[0]), $decoded_keys, true);
            }
        );
    }

    /**
     * 根据键名键值创建参数段
     * @param string      $key   键名
     * @param string|null $value 键值，为null则不添加
     * @return string
     */
    private static function generateQueryString(string $key, ?string $value): string
    {
        $query_string = strtr($key, self::$replaceQuery);
        if ($value !== null) {
            $query_string .= '=' . strtr($value, self::$replaceQuery);
        }
        return $query_string;
    }

    /**
     * 根据提供的各部件创建一个URI
     * @param array $parts `parse_url`方法解析出的各部件
     * @return Uri
     */
    public static function fromParts(array $parts): Uri
    {
        $uri = new self();
        $uri->applyParts($parts);
        $uri->validateState();

        return $uri;
    }

    /**
     * 对URI信息进行整理应用
     * @param array $parts 使用parse_url获取到的URI信息
     */
    private function applyParts(array $parts)
    {
        $this->scheme = isset($parts['scheme']) ? $this->filterScheme($parts['scheme']) : '';
        $this->userInfo = isset($parts['user']) ? $this->filterUserInfoComponent($parts['user']) : '';
        $this->host = isset($parts['host']) ? $this->filterHost($parts['host']) : '';
        $this->port = isset($parts['port']) ? $this->filterPort($parts['port']) : null;
        $this->path = isset($parts['path']) ? $this->filterPath($parts['path']) : '';
        $this->query = isset($parts['query']) ? $this->filterQueryAndFragment($parts['query']) : '';
        $this->fragment = isset($parts['fragment']) ? $this->filterQueryAndFragment($parts['fragment']) : '';
        if (isset($parts['pass'])) {
            $this->userInfo .= ':' . $this->filterUserInfoComponent($parts['pass']);
        }

        $this->removeDefaultPort();
    }

    /**
     * 处理协议部分
     * @param string $scheme 协议
     * @return string
     */
    private function filterScheme(string $scheme): string
    {
        return strtolower($scheme);
    }

    /**
     * 处理用户部分
     * @param string $component 用户信息
     * @return string
     */
    private function filterUserInfoComponent(string $component): string
    {
        $callback = function (array $match) {
            return rawurlencode($match[0]);
        };
        return preg_replace_callback(
            '/([^%' . self::$charUnreserved . self::$charSubDelims . ']+|%(?![A-Fa-f0-9]{2}))/',
            $callback,
            $component,
            1
        );
    }

    /**
     * 处理主机名部分
     * @param string $host 主机名
     * @return string
     */
    private function filterHost(string $host): string
    {
        return strtolower($host);
    }

    /**
     * 处理端口部分
     * @param int|string|null $port 端口
     * @return int|null
     */
    private function filterPort($port): ?int
    {
        if ($port === null) {
            return null;
        }

        $port = (int)$port;
        if (0 > $port || 0xffff < $port) {
            throw new InvalidArgumentException(
                sprintf('Invalid port: %d. Must be between 0 and 65535', $port)
            );
        }

        return $port;
    }

    /**
     * 处理路径部分
     * @param string $path 路径
     * @return string
     */
    private function filterPath(string $path): string
    {
        $callback = function (array $match) {
            return rawurlencode($match[0]);
        };
        return preg_replace_callback(
            '/([^' . self::$charUnreserved . self::$charSubDelims . '%:@\/]++|%(?![A-Fa-f0-9]{2}))/',
            $callback,
            $path,
            1
        );
    }

    /**
     * 处理参数部分
     * @param string $str 参数
     * @return string
     */
    private function filterQueryAndFragment(string $str): string
    {
        $callback = function (array $match) {
            return rawurlencode($match[0]);
        };
        return preg_replace_callback(
            '/([^' . self::$charUnreserved . self::$charSubDelims . '%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/',
            $callback,
            $str,
            1
        );
    }

    /**
     * 移除默认端口
     */
    private function removeDefaultPort()
    {
        if ($this->port !== null && $this->isDefaultPort()) {
            $this->port = null;
        }
    }

    /**
     * 检测各参数合法性
     */
    private function validateState()
    {
        if ($this->host === '' && ($this->scheme === 'http' || $this->scheme === 'https')) {
            $this->host = self::HTTP_DEFAULT_HOST;
        }

        if ($this->getAuthority() === '') {
            if (0 === strpos($this->path, '//')) {
                throw new InvalidArgumentException('The path of a URI without an authority must not start with two slashes "//"');
            }
            if ($this->scheme === '' && false !== strpos(explode('/', $this->path, 2)[0], ':')) {
                throw new InvalidArgumentException('A relative URI must not have a path beginning with a segment containing a colon');
            }
        } elseif (isset($this->path[0]) && $this->path[0] !== '/') {
            throw new InvalidArgumentException('The path of a URI with an authority must start with a slash "/" or be empty');
        }
    }

    /**
     * 标准化URI
     * @param int $flags 选项
     * @return UriInterface
     */
    public function normalize(int $flags = self::PRESERVING_NORMALIZATIONS): UriInterface
    {
        $uri = clone $this;
        if ($flags & self::CAPITALIZE_PERCENT_ENCODING) {
            $uri = $uri->capitalizePercentEncoding();
        }

        if ($flags & self::DECODE_UNRESERVED_CHARACTERS) {
            $uri = $uri->decodeUnreservedCharacters();
        }

        if ($flags & self::CONVERT_EMPTY_PATH && $uri->getPath() === '' && ($uri->getScheme() === 'http' || $uri->getScheme() === 'https')) {
            $uri = $uri->withPath('/');
        }

        if ($flags & self::REMOVE_DEFAULT_HOST && $uri->getScheme() === 'file' && $uri->getHost() === self::HTTP_DEFAULT_HOST) {
            $uri = $uri->withHost('');
        }

        if ($flags & self::REMOVE_DEFAULT_PORT && $uri->getPort() !== null && $uri->isDefaultPort()) {
            $uri = $uri->withPort(null);
        }

        if ($flags & self::REMOVE_DOT_SEGMENTS && !$uri->isRelativePathReference()) {
            $uri = $uri->withPath(self::removeDotSegments($uri->getPath()));
        }

        if ($flags & self::REMOVE_DUPLICATE_SLASHES) {
            $uri = $uri->withPath(preg_replace('#//++#', '/', $uri->getPath()));
        }

        if ($flags & self::SORT_QUERY_PARAMETERS && $uri->getQuery() !== '') {
            $query_key_values = explode('&', $uri->getQuery());
            sort($query_key_values);
            $uri = $uri->withQuery(implode('&', $query_key_values));
        }

        return $uri;
    }

    /**
     * 一个百分比编码的三连音中的所有字母(例如“%3A”)是不区分大小写的，应大写
     * @return UriInterface
     */
    private function capitalizePercentEncoding(): UriInterface
    {
        $regex = '/(?:%[A-Fa-f0-9]{2})++/';

        $callback = function (array $match) {
            return strtoupper($match[0]);
        };

        return
            $this
                ->withPath(preg_replace_callback($regex, $callback, $this->getPath()))
                ->withQuery(preg_replace_callback($regex, $callback, $this->getQuery()));
    }

    /**
     * 解码未保留字符的百分比编码字节。
     * @return UriInterface
     */
    private function decodeUnreservedCharacters(): UriInterface
    {
        $regex = '/%(?:2D|2E|5F|7E|3[0-9]|[46][1-9A-F]|[57][0-9A])/i';

        $callback = function (array $match) {
            return rawurldecode($match[0]);
        };

        return
            $this
                ->withPath(preg_replace_callback($regex, $callback, $this->getPath()))
                ->withQuery(preg_replace_callback($regex, $callback, $this->getQuery()));
    }

    /**
     * 将相对URI转换为根据基准URI解析的新URI。
     * @param UriInterface $base 基准URI
     * @param UriInterface $rel  相对URI
     * @return UriInterface
     */
    public static function resolve(UriInterface $base, UriInterface $rel): UriInterface
    {
        if ((string)$rel === '') {
            // we can simply return the same base URI instance for this same-document reference
            return $base;
        }

        if ($rel->getScheme() != '') {
            return $rel->withPath(self::removeDotSegments($rel->getPath()));
        }

        if ($rel->getAuthority() != '') {
            $target_authority = $rel->getAuthority();
            $target_path = self::removeDotSegments($rel->getPath());
            $target_query = $rel->getQuery();
        } else {
            $target_authority = $base->getAuthority();
            if ($rel->getPath() === '') {
                $target_path = $base->getPath();
                $target_query = $rel->getQuery() != '' ? $rel->getQuery() : $base->getQuery();
            } else {
                if ($rel->getPath()[0] === '/') {
                    $target_path = $rel->getPath();
                } else {
                    if ($target_authority != '' && $base->getPath() === '') {
                        $target_path = '/' . $rel->getPath();
                    } else {
                        $last_slash_pos = strrpos($base->getPath(), '/');
                        if ($last_slash_pos === false) {
                            $target_path = $rel->getPath();
                        } else {
                            $target_path = substr($base->getPath(), 0, $last_slash_pos + 1) . $rel->getPath();
                        }
                    }
                }
                $target_path = self::removeDotSegments($target_path);
                $target_query = $rel->getQuery();
            }
        }

        return new static(self::composeComponents(
            $base->getScheme(),
            $target_authority,
            $target_path,
            $target_query,
            $rel->getFragment()
        ));
    }

    /**
     * 根据基准URI返回目标URI的相对路径URI
     * @param UriInterface $base   基准URI
     * @param UriInterface $target 目标URI
     * @return UriInterface
     */
    public static function relativize(UriInterface $base, UriInterface $target): UriInterface
    {
        if ($target->getScheme() !== '' && ($base->getScheme() !== $target->getScheme() || $target->getAuthority() === '' && $base->getAuthority() !== '')) {
            return $target;
        }

        if ($target->isRelativePathReference()) {
            // As the target is already highly relative we return it as-is. It would be possible to resolve
            // the target with `$target = self::resolve($base, $target);` and then try make it more relative
            // by removing a duplicate query. But let's not do that automatically.
            return $target;
        }

        if ($target->getAuthority() !== '' && $base->getAuthority() !== $target->getAuthority()) {
            return $target->withScheme('');
        }

        // We must remove the path before removing the authority because if the path starts with two slashes, the URI
        // would turn invalid. And we also cannot set a relative path before removing the authority, as that is also
        // invalid.
        $empty_path_uri = $target->withScheme('')->withPath('')->withUserInfo('')->withPort(null)->withHost('');

        if ($base->getPath() !== $target->getPath()) {
            return $empty_path_uri->withPath(self::getRelativePath($base, $target));
        }

        if ($base->getQuery() === $target->getQuery()) {
            // Only the target fragment is left. And it must be returned even if base and target fragment are the same.
            return $empty_path_uri->withQuery('');
        }

        // If the base URI has a query but the target has none, we cannot return an empty path reference as it would
        // inherit the base query component when resolving.
        if ($target->getQuery() === '') {
            $segments = explode('/', $target->getPath());
            $last_segment = end($segments);
            return $empty_path_uri->withPath($last_segment === '' ? './' : $last_segment);
        }

        return $empty_path_uri;
    }

    /**
     * 根据基准URI返回目标URI的相对路径
     * @param UriInterface $base   基准URI
     * @param UriInterface $target 目标URI
     * @return string
     */
    private static function getRelativePath(UriInterface $base, UriInterface $target): string
    {
        $source_segments = explode('/', $base->getPath());
        $target_segments = explode('/', $target->getPath());
        array_pop($source_segments);
        $tglast_segment = array_pop($target_segments);
        foreach ($source_segments as $i => $segment) {
            if (isset($target_segments[$i]) && $segment === $target_segments[$i]) {
                unset($source_segments[$i], $target_segments[$i]);
            } else {
                break;
            }
        }
        $target_segments[] = $tglast_segment;
        $relative_path = str_repeat('../', count($source_segments)) . implode('/', $target_segments);

        // A reference to am empty last segment or an empty first sub-segment must be prefixed with "./".
        // This also applies to a segment with a colon character (e.g., "file:colon") that cannot be used
        // as the first segment of a relative-path reference, as it would be mistaken for a scheme name.
        if ('' === $relative_path || false !== strpos(explode('/', $relative_path, 2)[0], ':')) {
            $relative_path = "./$relative_path";
        } elseif ('/' === $relative_path[0]) {
            if ($base->getAuthority() != '' && $base->getPath() === '') {
                // In this case an extra slash is added by resolve() automatically. So we must not add one here.
                $relative_path = ".$relative_path";
            } else {
                $relative_path = "./$relative_path";
            }
        }

        return $relative_path;
    }

    /**
     * 判断并转换URL为标准格式
     * @param string $url 待处理的URL
     * @return string|false 标准化后的URL，无效URL返回false
     * @todo 处理URL中的特殊字符
     */
    protected function normalizeUrl(string $url)
    {
        // 步骤1：验证是否为标准URL
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url; // 直接返回标准URL
        }

        $parts = explode('//', $url);
        $scheme = $parts[0];
        $others = $parts[1];
        $parts = explode('/', $others);


        $pathinfo = pathinfo($url);
        $path = $pathinfo['path'] ?? '';

        // 步骤2：尝试修复非标准URL
        // 2.1 添加缺失的协议
        if (!parse_url($url, PHP_URL_SCHEME)) {
            $url = "https://" . ltrim($url, '/');
        }

        // 2.3 编码特殊字符
        $parts = parse_url($url);
        if ($parts === false) return false;

        // 编码路径中的特殊字符
        if (isset($parts['path'])) {
            $pathParts = explode('/', $parts['path']);
            foreach ($pathParts as &$part) {
                $part = rawurlencode($part);
            }
            $parts['path'] = implode('/', $pathParts);
        }

        // 编码查询参数
        if (isset($parts['query'])) {
            parse_str($parts['query'], $queryParams);
            array_walk($queryParams, function (&$value, $key) {
                $value = rawurlencode($value);
                $value = rawurldecode($value);
                print_r($value);
            });
            $parts['query'] = http_build_query($queryParams);
        }

        // 步骤3：重构标准化URL
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';
        $path = $parts['path'] ?? '';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . rawurlencode($parts['fragment']) : '';

        // 主机名转小写（标准要求）
        $host = strtolower($host);

        // 构建最终URL
        $standardUrl = sprintf(
            "%s://%s%s%s%s",
            $scheme,
            $host,
            $path,
            $query,
            $fragment
        );

        // 二次验证确保转换有效
        return filter_var($standardUrl, FILTER_VALIDATE_URL) ? $standardUrl : false;
    }
}
