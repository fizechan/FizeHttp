<?php

namespace fize\http;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use fize\misc\Preg;

/**
 * URI 对象
 */
class Uri implements UriInterface
{

    /**
     * 默认的规范化，只包括保留语义的规范化
     */
    const PRESERVING_NORMALIZATIONS = 63;

    /**
     * 一个百分比编码的三连音中的所有字母(例如“%3A”)是不区分大小写的，应该大写。
     *
     * 例如: http://example.org/a%c2%b1b → http://example.org/a%C2%B1b
     */
    const CAPITALIZE_PERCENT_ENCODING = 1;

    /**
     * 解码未保留字符的百分比编码字节。
     *
     * 例如: http://example.org/%7Eusern%61me/ → http://example.org/~username/
     */
    const DECODE_UNRESERVED_CHARACTERS = 2;

    /**
     * 将http和https uri的空路径转换为“/”。
     *
     * 例如: http://example.org → http://example.org/
     */
    const CONVERT_EMPTY_PATH = 4;

    /**
     * 从URI中删除给定URI方案的默认主机。
     *
     * 例如: file://localhost/myfile → file:///myfile
     */
    const REMOVE_DEFAULT_HOST = 8;

    /**
     * 从URI中删除给定URI方案的默认端口。
     *
     * 例如: http://example.org:80/ → http://example.org/
     */
    const REMOVE_DEFAULT_PORT = 16;

    /**
     * 移除不必要的相对路径
     *
     * 例如: http://example.org/../a/b/../c/./d.html → http://example.org/a/c/d.html
     */
    const REMOVE_DOT_SEGMENTS = 32;

    /**
     * 包含两个或多个相邻斜杠的路径被转换为一个斜杠。
     *
     * 例如: http://example.org//foo///bar.html → http://example.org/foo/bar.html
     */
    const REMOVE_DUPLICATE_SLASHES = 64;

    /**
     * 将查询参数及其值按字母顺序排序。
     * URI中参数的顺序可能很重要(这不是由标准定义的)。
     * 所以这种标准化是不安全的，可能会改变URI的语义。
     *
     * 例如: ?lang=en&article=fred → ?article=fred&lang=en
     */
    const SORT_QUERY_PARAMETERS = 128;

    /**
     * 默认主机名
     */
    const HTTP_DEFAULT_HOST = 'localhost';

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
     * @param string $uri 待解析URI
     * @throws InvalidArgumentException
     */
    public function __construct(string $uri = '')
    {
        if ($uri != '') {
            $parts = parse_url($uri);
            if ($parts === false) {
                throw new InvalidArgumentException("Unable to parse URI: $uri");
            }
            $this->applyParts($parts);
        }
    }

    /**
     * 转字符串
     * @return string
     */
    public function __toString()
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
    public function withScheme($scheme): Uri
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
    public function withUserInfo($user, $password = null): Uri
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
    public function withHost($host): Uri
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
     * @param int|null $port 端口
     * @return static
     */
    public function withPort($port): Uri
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
    public function withPath($path): Uri
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
    public function withQuery($query): Uri
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
    public function withFragment($fragment): Uri
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
     * @param UriInterface $uri URI对象
     * @return bool
     * @link https://tools.ietf.org/html/rfc3986#section-4
     */
    public static function isAbsolute(UriInterface $uri): bool
    {
        return $uri->getScheme() !== '';
    }

    /**
     * 判断URI是否为网络路径引用
     *
     * 以两个斜杠字符开头的相对引用称为网络路径引用
     * @param UriInterface $uri URI对象
     * @return bool
     * @link https://tools.ietf.org/html/rfc3986#section-4.2
     */
    public static function isNetworkPathReference(UriInterface $uri): bool
    {
        return $uri->getScheme() === '' && $uri->getAuthority() !== '';
    }

    /**
     * 判断URI是否为绝对路径
     * @param UriInterface $uri URI对象
     * @return bool
     * @link https://tools.ietf.org/html/rfc3986#section-4.3
     */
    public static function isAbsolutePathReference(UriInterface $uri): bool
    {
        return $uri->getScheme() === '' && $uri->getAuthority() === '' && isset($uri->getPath()[0]) && $uri->getPath()[0] === '/';
    }

    /**
     * 判断URI是否为相对路径
     * @param UriInterface $uri
     * @return bool
     * @link https://tools.ietf.org/html/rfc3986#section-4.2
     */
    public static function isRelativePathReference(UriInterface $uri): bool
    {
        return $uri->getScheme() === '' && $uri->getAuthority() === '' && (!isset($uri->getPath()[0]) || $uri->getPath()[0] !== '/');
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
     * @param UriInterface $uri URI对象
     * @return bool
     */
    public static function isDefaultPort(UriInterface $uri): bool
    {
        $port = $uri->getPort();
        if ($port === null) {
            return true;
        }

        $scheme = $uri->getScheme();
        if (isset(self::$defaultPorts[$scheme]) && $port === self::$defaultPorts[$scheme]) {
            return true;
        }

        return false;
    }

    /**
     * 从路径中删除点段并返回新路径。
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

        $newPath = implode('/', $results);

        if ($path[0] === '/' && (!isset($newPath[0]) || $newPath[0] !== '/')) {
            // Re-add the leading slash if necessary for cases like "/.."
            $newPath = '/' . $newPath;
        } elseif ($newPath !== '' && ($segment === '.' || $segment === '..')) {
            // Add the trailing slash if necessary
            // If newPath is not empty, then $segment must be set and is the last segment from the foreach
            $newPath .= '/';
        }

        return $newPath;
    }

    /**
     * 移除指定参数
     * @param UriInterface $uri URI对象
     * @param string       $key 键名
     * @return UriInterface
     */
    public static function withoutQueryValue(UriInterface $uri, string $key): UriInterface
    {
        $result = self::getFilteredQueryString($uri, [$key]);

        return $uri->withQuery(implode('&', $result));
    }

    /**
     * 添加指定参数
     * @param UriInterface $uri   URI对象
     * @param string       $key   键名
     * @param string|null  $value 键值
     * @return UriInterface
     */
    public static function withQueryValue(UriInterface $uri, string $key, ?string $value): UriInterface
    {
        $result = self::getFilteredQueryString($uri, [$key]);

        $result[] = self::generateQueryString($key, $value);

        return $uri->withQuery(implode('&', $result));
    }

    /**
     * 添加多个参数
     * @param UriInterface $uri           URI对象
     * @param array        $keyValueArray 参数键值对
     * @return UriInterface
     */
    public static function withQueryValues(UriInterface $uri, array $keyValueArray): UriInterface
    {
        $result = self::getFilteredQueryString($uri, array_keys($keyValueArray));

        foreach ($keyValueArray as $key => $value) {
            $result[] = self::generateQueryString($key, $value);
        }

        return $uri->withQuery(implode('&', $result));
    }

    /**
     * 获取参数列表
     * @param UriInterface $uri  URI对象
     * @param array        $keys 键名在该数组内的将不返回
     * @return array 数组项格式为x=y
     */
    private static function getFilteredQueryString(UriInterface $uri, array $keys): array
    {
        $current = $uri->getQuery();

        if ($current === '') {
            return [];
        }

        $decodedKeys = array_map('rawurldecode', $keys);

        return array_filter(
            explode('&', $current),
            function ($part) use ($decodedKeys) {
                return !in_array(rawurldecode(explode('=', $part)[0]), $decodedKeys, true);
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
        $queryString = strtr($key, self::$replaceQuery);

        if ($value !== null) {
            $queryString .= '=' . strtr($value, self::$replaceQuery);
        }

        return $queryString;
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
        if (!is_string($scheme)) {
            throw new InvalidArgumentException('Scheme must be a string');
        }

        return strtolower($scheme);
    }

    /**
     * 处理用户部分
     * @param string $component 用户信息
     * @return string
     */
    private function filterUserInfoComponent(string $component): string
    {
        if (!is_string($component)) {
            throw new InvalidArgumentException('User info must be a string');
        }

        return preg_replace_callback(
            '/(?:[^%' . self::$charUnreserved . self::$charSubDelims . ']+|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'rawurlencodeMatchZero'],
            $component
        );
    }

    /**
     * 处理主机名部分
     * @param string $host 主机名
     * @return string
     */
    private function filterHost(string $host): string
    {
        if (!is_string($host)) {
            throw new InvalidArgumentException('Host must be a string');
        }

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
        if (!is_string($path)) {
            throw new InvalidArgumentException('Path must be a string');
        }

        return preg_replace_callback(
            '/(?:[^' . self::$charUnreserved . self::$charSubDelims . '%:@\/]++|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'rawurlencodeMatchZero'],
            $path
        );
    }

    /**
     * 处理参数部分
     * @param string $str 参数
     * @return string
     */
    private function filterQueryAndFragment(string $str): string
    {
        if (!is_string($str)) {
            throw new InvalidArgumentException('Query and fragment must be a string');
        }

        return preg_replace_callback(
            '/(?:[^' . self::$charUnreserved . self::$charSubDelims . '%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'rawurlencodeMatchZero'],
            $str
        );
    }

    /**
     * 移除默认端口
     */
    private function removeDefaultPort()
    {
        if ($this->port !== null && self::isDefaultPort($this)) {
            $this->port = null;
        }
    }

    /**
     * 对第一个匹配项进行rawurlencode
     * @param array $match 匹配项
     * @return string
     */
    private function rawurlencodeMatchZero(array $match): string
    {
        return rawurlencode($match[0]);
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
     * @param UriInterface $uri   URI对象
     * @param int          $flags 选项
     * @return UriInterface
     */
    public static function normalize(UriInterface $uri, int $flags = self::PRESERVING_NORMALIZATIONS): UriInterface
    {
        if ($flags & self::CAPITALIZE_PERCENT_ENCODING) {
            $uri = self::capitalizePercentEncoding($uri);
        }

        if ($flags & self::DECODE_UNRESERVED_CHARACTERS) {
            $uri = self::decodeUnreservedCharacters($uri);
        }

        if ($flags & self::CONVERT_EMPTY_PATH && $uri->getPath() === '' && ($uri->getScheme() === 'http' || $uri->getScheme() === 'https')) {
            $uri = $uri->withPath('/');
        }

        if ($flags & self::REMOVE_DEFAULT_HOST && $uri->getScheme() === 'file' && $uri->getHost() === self::HTTP_DEFAULT_HOST) {
            $uri = $uri->withHost('');
        }

        if ($flags & self::REMOVE_DEFAULT_PORT && $uri->getPort() !== null && self::isDefaultPort($uri)) {
            $uri = $uri->withPort(null);
        }

        if ($flags & self::REMOVE_DOT_SEGMENTS && !self::isRelativePathReference($uri)) {
            $uri = $uri->withPath(self::removeDotSegments($uri->getPath()));
        }

        if ($flags & self::REMOVE_DUPLICATE_SLASHES) {
            $uri = $uri->withPath(Preg::replace('#//++#', '/', $uri->getPath()));
        }

        if ($flags & self::SORT_QUERY_PARAMETERS && $uri->getQuery() !== '') {
            $queryKeyValues = explode('&', $uri->getQuery());
            sort($queryKeyValues);
            $uri = $uri->withQuery(implode('&', $queryKeyValues));
        }

        return $uri;
    }

    /**
     * 一个百分比编码的三连音中的所有字母(例如“%3A”)是不区分大小写的，应大写
     * @param UriInterface $uri URI对象
     * @return UriInterface
     */
    private static function capitalizePercentEncoding(UriInterface $uri): UriInterface
    {
        $regex = '/(?:%[A-Fa-f0-9]{2})++/';

        $callback = function (array $match) {
            return strtoupper($match[0]);
        };

        return
            $uri
                ->withPath(Preg::replaceCallback($regex, $callback, $uri->getPath()))
                ->withQuery(Preg::replaceCallback($regex, $callback, $uri->getQuery()));
    }

    /**
     * 解码未保留字符的百分比编码字节。
     * @param UriInterface $uri URI对象
     * @return UriInterface
     */
    private static function decodeUnreservedCharacters(UriInterface $uri): UriInterface
    {
        $regex = '/%(?:2D|2E|5F|7E|3[0-9]|[46][1-9A-F]|[57][0-9A])/i';

        $callback = function (array $match) {
            return rawurldecode($match[0]);
        };

        return
            $uri
                ->withPath(Preg::replaceCallback($regex, $callback, $uri->getPath()))
                ->withQuery(Preg::replaceCallback($regex, $callback, $uri->getQuery()));
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
            $targetAuthority = $rel->getAuthority();
            $targetPath = self::removeDotSegments($rel->getPath());
            $targetQuery = $rel->getQuery();
        } else {
            $targetAuthority = $base->getAuthority();
            if ($rel->getPath() === '') {
                $targetPath = $base->getPath();
                $targetQuery = $rel->getQuery() != '' ? $rel->getQuery() : $base->getQuery();
            } else {
                if ($rel->getPath()[0] === '/') {
                    $targetPath = $rel->getPath();
                } else {
                    if ($targetAuthority != '' && $base->getPath() === '') {
                        $targetPath = '/' . $rel->getPath();
                    } else {
                        $lastSlashPos = strrpos($base->getPath(), '/');
                        if ($lastSlashPos === false) {
                            $targetPath = $rel->getPath();
                        } else {
                            $targetPath = substr($base->getPath(), 0, $lastSlashPos + 1) . $rel->getPath();
                        }
                    }
                }
                $targetPath = self::removeDotSegments($targetPath);
                $targetQuery = $rel->getQuery();
            }
        }

        return new static(self::composeComponents(
            $base->getScheme(),
            $targetAuthority,
            $targetPath,
            $targetQuery,
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

        if (self::isRelativePathReference($target)) {
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
        $emptyPathUri = $target->withScheme('')->withPath('')->withUserInfo('')->withPort(null)->withHost('');

        if ($base->getPath() !== $target->getPath()) {
            return $emptyPathUri->withPath(self::getRelativePath($base, $target));
        }

        if ($base->getQuery() === $target->getQuery()) {
            // Only the target fragment is left. And it must be returned even if base and target fragment are the same.
            return $emptyPathUri->withQuery('');
        }

        // If the base URI has a query but the target has none, we cannot return an empty path reference as it would
        // inherit the base query component when resolving.
        if ($target->getQuery() === '') {
            $segments = explode('/', $target->getPath());
            $lastSegment = end($segments);

            return $emptyPathUri->withPath($lastSegment === '' ? './' : $lastSegment);
        }

        return $emptyPathUri;
    }

    /**
     * 根据基准URI返回目标URI的相对路径
     * @param UriInterface $base   基准URI
     * @param UriInterface $target 目标URI
     * @return string
     */
    private static function getRelativePath(UriInterface $base, UriInterface $target): string
    {
        $sourceSegments = explode('/', $base->getPath());
        $targetSegments = explode('/', $target->getPath());
        array_pop($sourceSegments);
        $targetLastSegment = array_pop($targetSegments);
        foreach ($sourceSegments as $i => $segment) {
            if (isset($targetSegments[$i]) && $segment === $targetSegments[$i]) {
                unset($sourceSegments[$i], $targetSegments[$i]);
            } else {
                break;
            }
        }
        $targetSegments[] = $targetLastSegment;
        $relativePath = str_repeat('../', count($sourceSegments)) . implode('/', $targetSegments);

        // A reference to am empty last segment or an empty first sub-segment must be prefixed with "./".
        // This also applies to a segment with a colon character (e.g., "file:colon") that cannot be used
        // as the first segment of a relative-path reference, as it would be mistaken for a scheme name.
        if ('' === $relativePath || false !== strpos(explode('/', $relativePath, 2)[0], ':')) {
            $relativePath = "./$relativePath";
        } elseif ('/' === $relativePath[0]) {
            if ($base->getAuthority() != '' && $base->getPath() === '') {
                // In this case an extra slash is added by resolve() automatically. So we must not add one here.
                $relativePath = ".$relativePath";
            } else {
                $relativePath = "./$relativePath";
            }
        }

        return $relativePath;
    }
}
