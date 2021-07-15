<?php

namespace fize\http;

/**
 * 共享 CURL
 */
class CurlShare
{

    /**
     *
     * @var resource 当前会话句柄
     */
    protected $handle;

    /**
     * 构造函数
     * @param array $options 指定选项
     */
    public function __construct(array $options = [])
    {
        $this->handle = $this->init();
        foreach ($options as $option => $value) {
            $this->setopt($option, $value);
        }
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        if ($this->handle && get_resource_type($this->handle) == "curl_share") {
            $this->close();
        }
        $this->handle = null;
    }

    /**
     * 关闭当前会话
     */
    public function close()
    {
        curl_share_close($this->handle);
    }

    /**
     * 返回最后一次的错误号
     * @return int
     */
    public function errno(): int
    {
        return curl_share_errno($this->handle);
    }

    /**
     * 返回一个 CURL 共享句柄
     * @return resource
     */
    protected function init()
    {
        return curl_share_init();
    }

    /**
     * 为当前传输会话设置选项
     * @param int   $option 需要设置的 CURLOPT_XXX 选项。
     * @param mixed $value  将设置在 option 选项上的值。
     * @return bool
     */
    public function setopt(int $option, $value): bool
    {
        return curl_share_setopt($this->handle, $option, $value);
    }

    /**
     * 根据错误码返回错误描述
     * @param int $errornum 返回的错误码
     * @return string
     */
    public static function strerror(int $errornum): string
    {
        return curl_share_strerror($errornum);
    }

    /**
     * 获取原始句柄
     * @return resource
     */
    public function getHandle()
    {
        return $this->handle;
    }
}