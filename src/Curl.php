<?php

namespace fize\http;

use CURLFile;

/**
 * CURL
 */
class Curl
{

    /**
     *
     * @var resource 当前会话句柄
     */
    protected $handle;

    /**
     * 构造函数
     * @param string|null $url     指定会话链接
     * @param array       $options 指定选项
     */
    public function __construct(string $url = null, array $options = [])
    {
        $this->handle = $this->init($url);
        if (!is_null($url)) {
            $this->setopt(CURLOPT_URL, $url);
        }
        if (!empty($options)) {
            $this->setoptArray($options);
        }
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        if ($this->handle && get_resource_type($this->handle) == 'curl') {
            $this->close();
        }
        $this->handle = null;
    }

    /**
     * 关闭当前会话
     */
    public function close()
    {
        curl_close($this->handle);
    }

    /**
     * 复制当前 CURL 句柄和其所有选项
     * @return resource
     */
    public function copyHandle()
    {
        return curl_copy_handle($this->handle);
    }

    /**
     * 返回最后一次的错误号
     * @return int
     */
    public function errno(): int
    {
        return curl_errno($this->handle);
    }

    /**
     * 返回最近一次错误的字符串
     * @return string
     */
    public function error(): string
    {
        return curl_error($this->handle);
    }

    /**
     * 使用 URL 编码给定的字符串
     * @param string $str 给定的字符串
     * @return string|false
     */
    public function escape(string $str)
    {
        return curl_escape($this->handle, $str);
    }

    /**
     * 执行当前会话
     * @return string|bool 执行结果，错误返回false
     */
    public function exec()
    {
        return curl_exec($this->handle);
    }

    /**
     * 创建一个用于上传的 CURLFile 对象
     * @param string $filename 文件路径
     * @param string $mimetype MIME
     * @param string $postname 文件域表单名称
     * @return CURLFile
     */
    public static function fileCreate(string $filename, string $mimetype, string $postname): CURLFile
    {
        return curl_file_create($filename, $mimetype, $postname);
    }

    /**
     * 获取当前 cURL 连接资源句柄的信息
     * @param int|null $opt 参数常量
     * @return array|string
     */
    public function getinfo(int $opt = null)
    {
        if (is_null($opt)) {
            return curl_getinfo($this->handle);
        } else {
            return curl_getinfo($this->handle, $opt);
        }
    }

    /**
     * 返回一个 CURL 句柄
     * @param string|null $url 指定链接 URL
     * @return resource
     */
    protected function init(string $url = null)
    {
        return curl_init($url);
    }

    /**
     * 暂停或解除暂停当前会话
     * @param int $bitmask CURLPAUSE_*常量之一
     * @return int
     */
    public function pause(int $bitmask): int
    {
        return curl_pause($this->handle, $bitmask);
    }

    /**
     * 重置当前会话的所有设置
     */
    public function reset()
    {
        curl_reset($this->handle);
    }

    /**
     * 为当前传输会话批量设置选项
     * @param array $options 要设置的选项数组
     * @return bool
     */
    public function setoptArray(array $options): bool
    {
        return curl_setopt_array($this->handle, $options);
    }

    /**
     * 为当前传输会话设置选项
     * @param int   $option 需要设置的 CURLOPT_XXX 选项。
     * @param mixed $value  将设置在 option 选项上的值。
     * @return bool
     */
    public function setopt(int $option, $value): bool
    {
        return curl_setopt($this->handle, $option, $value);
    }

    /**
     * 根据错误码返回错误描述
     * @param int $errornum 返回的错误码
     * @return string
     */
    public static function strerror(int $errornum): string
    {
        return curl_strerror($errornum);
    }

    /**
     * 解码给定的 URL 编码的字符串
     * @param string $str 待解码字符串
     * @return string
     */
    public function unescape(string $str): string
    {
        return curl_unescape($this->handle, $str);
    }

    /**
     * 获取 cURL 版本信息
     * @return array
     */
    public static function version(): array
    {
        return curl_version();
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
