<?php

namespace Fize\Http;

use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * HTTP 消息
 */
abstract class Message implements MessageInterface
{

    /**
     * @var string HTTP 协议版本
     */
    protected $protocolVersion;

    /**
     * @var array 报头信息
     */
    protected $headers = [];

    /**
     * @var Stream 数据流
     */
    protected $stream;

    /**
     * 获取 HTTP 协议版本信息
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * 返回指定 HTTP 版本号的消息实例
     * @param string $version HTTP 协议版本
     * @return static
     */
    public function withProtocolVersion(string $version): Message
    {
        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    /**
     * 获取所有的报头信息
     *
     * 返回的数组中，「键」代表单条报头信息的名字，「值」是以数组形式返回的
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * 检查是否报头信息中包含有此名称的值
     *
     * 键名不区分大小写
     * @param string $name 键名
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        $name = $this->getRealHeaderName($name);
        return !is_null($name);
    }

    /**
     * 根据键名获取一条报头信息，以数组形式返回
     *
     * 键名不区分大小写
     * @param string $name
     * @return string[] 不存在则返回空数组
     */
    public function getHeader(string $name): array
    {
        $name = $this->getRealHeaderName($name);
        if (is_null($name)) {
            return [];
        }
        return $this->headers[$name];
    }

    /**
     * 根据键名获取一条报头信息，以逗号分隔的形式返回
     *
     * 键名不区分大小写
     * @param string $name
     * @return string
     */
    public function getHeaderLine(string $name): string
    {
        return implode(',', $this->getHeader($name));
    }

    /**
     * 返回替换指定报头信息「键/值」对的消息实例
     *
     * 键名不区分大小写
     * @param string          $name  键名
     * @param string|string[] $value 报头信息或报头信息数组
     * @return static
     */
    public function withHeader(string $name, $value): Message
    {
        $this->assertHeaderName($name);
        $value = $this->normalizeHeaderValue($value);
        $orig_name = $this->getRealHeaderName($name);
        $new = clone $this;
        if ($orig_name) {
            unset($new->headers[$orig_name]);
        }
        $new->headers[$name] = $value;
        return $new;
    }

    /**
     * 返回一个报头信息增量的 HTTP 消息实例
     * @param string          $name  键名
     * @param string|string[] $value 报头信息或报头信息数组
     * @return static
     */
    public function withAddedHeader(string $name, $value): Message
    {
        $this->assertHeaderName($name);
        $value = $this->normalizeHeaderValue($value);
        $orig_name = $this->getRealHeaderName($name);

        $new = clone $this;
        if ($orig_name) {
            $orig_value = $this->headers[$orig_name];
            unset($new->headers[$orig_name]);

            $arr = array_flip($orig_value) + array_flip($value);
            $new->headers[$name] = array_keys($arr);
//            $new->headers[$name] = array_merge($orig_value, $value);
        } else {
            $new->headers[$name] = $value;
        }
        return $new;
    }

    /**
     * 返回被移除掉指定报头信息的 HTTP 消息实例
     * @param string $name 键名
     * @return static
     */
    public function withoutHeader(string $name): Message
    {
        $orig_name = $this->getRealHeaderName($name);
        $new = clone $this;
        if ($orig_name) {
            unset($new->headers[$orig_name]);
        }
        return $new;
    }

    /**
     * 获取消息的内容
     * @return StreamInterface
     */
    public function getBody()
    {
        if (!$this->stream) {
            $resource = fopen('php://temp', 'r+');
            $this->stream = new Stream($resource);
        }

        return $this->stream;
    }

    /**
     * 返回指定内容的 HTTP 消息实例
     * @param StreamInterface $body 数据流形式的内容
     * @return static
     */
    public function withBody(StreamInterface $body): Message
    {
        $new = clone $this;
        $new->stream = $body;
        return $new;
    }

    /**
     * 根据键名返回报头对应的实际键名
     * @param string $name 键名
     * @return string|null 不存在该键名则返回 null
     */
    protected function getRealHeaderName(string $name): ?string
    {
        $name = strtolower($name);
        foreach (array_keys($this->headers) as $key) {
            if (strtolower($key) === $name) {
                return $key;
            }
        }
        return null;
    }

    /**
     * 变量作为报头信息键名的合法性
     * @param mixed $name 字段名
     */
    protected function assertHeaderName($name)
    {
        if (!is_string($name)) {
            $msg = sprintf(
                'Header name must be a string but %s provided.',
                is_object($name) ? get_class($name) : gettype($name)
            );
            throw new InvalidArgumentException($msg);
        }

        if ($name === '') {
            throw new InvalidArgumentException('Header name can not be empty.');
        }
    }

    /**
     * 规范化报头键值
     * @param string|string[] $value 键值数组或者键值
     *
     * @return array
     */
    protected function normalizeHeaderValue($value): array
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        if (count($value) === 0) {
            throw new InvalidArgumentException('Header value can not be an empty array.');
        }

        return $this->trimHeaderValues($value);
    }

    /**
     * 去除键值中的空格和换行符防止破坏协议
     * @param array $values
     * @return array
     */
    private function trimHeaderValues(array $values): array
    {
        return array_map(function ($value) {
            if (!is_scalar($value) && null !== $value) {
                throw new InvalidArgumentException(sprintf(
                    'Header value must be scalar or null but %s provided.',
                    is_object($value) ? get_class($value) : gettype($value)
                ));
            }

            return trim((string)$value, " \t");
        }, $values);
    }

    /**
     * 设置报头信息
     * @param array $headers 报头信息
     */
    protected function setHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            if (is_int($name)) {
                // Numeric array keys are converted to int by PHP but having a header name '123' is not forbidden by the spec
                // and also allowed in withHeader(). So we need to cast it to string again for the following assertion to pass.
                $name = (string)$name;
            }
            $this->assertHeaderName($name);
            $value = $this->normalizeHeaderValue($value);
            $orig_name = $this->getRealHeaderName($name);

            if ($orig_name) {
                $orig_value = $this->headers[$orig_name];
                unset($this->headers[$orig_name]);
                $this->headers[$name] = array_merge($orig_value, $value);
            } else {
                $this->headers[$name] = $value;
            }
        }
    }
}
