<?php

namespace fize\http;


use CURLFile;

/**
 * CURL 类
 * @todo 尚有几处未知的参数意义需要补齐
 */
class Curl
{

    /**
     *
     * @var string 当前会话链接
     */
    private $url = null;

    /**
     *
     * @var resource 当前会话句柄
     */
    private $handle;

    /**
     * @var array 当前会话设置数组
     */
    private $opt = [];

    /**
     * @var bool 当前会话是否可分享
     */
    private $share;


    /**
     * 构造函数
     * @param string $url 指定会话链接
     * @param array $opt 指定选项
     * @param bool $share 指明是否使用share
     */
    public function __construct($url = null, array $opt = [], $share = false)
    {
        $this->share = $share;
        $this->handle = $this->init($url);
        if (!empty($url)) {
            $this->url = $url;
            $this->setopt(CURLOPT_URL, $url);
        }
        if (!empty($opt)) {
            $this->setoptArray($opt);
        }
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        if ($this->handle && get_resource_type($this->handle) == "curl") {
            $this->close();
        }
    }

    /**
     * 获取当前会话句柄
     * @return resource
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * 关闭当前会话
     */
    public function close()
    {
        if ($this->share) {
            curl_share_close($this->handle);
        } else {
            curl_close($this->handle);
        }
        $this->handle = null;
        $this->opt = [];
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
    public function errno()
    {
        return curl_errno($this->handle);
    }

    /**
     * 返回最近一次错误的字符串
     * @return string
     */
    public function error()
    {
        return curl_error($this->handle);
    }

    /**
     * 使用 URL 编码给定的字符串
     * @param string $str 给定的字符串
     * @return string
     */
    public function escape($str)
    {
        return curl_escape($this->handle, $str);
    }

    /**
     * 执行当前会话
     * @return mixed 执行结果，错误返回false
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
    public static function fileCreate($filename, $mimetype, $postname)
    {
        return curl_file_create($filename, $mimetype, $postname);
    }

    /**
     * 获取当前 cURL 连接资源句柄的信息
     * @param int $opt 参数常量
     * @return mixed
     */
    public function getinfo($opt = null)
    {
        if (is_null($opt)) {
            return curl_getinfo($this->handle);
        } else {
            return curl_getinfo($this->handle, $opt);
        }
    }

    /**
     * 返回一个 CURL 句柄
     * @param string $url 指定链接 URL
     * @return resource
     */
    public function init($url = null)
    {
        if ($this->share) {
            return curl_share_init();
        } else {
            return curl_init($url);
        }
    }

    /**
     * 以新句柄方式设置当前句柄
     * @param resource $handle 要设置的句柄
     */
    public function setHandle(&$handle)
    {
        $this->handle = $handle;
        $this->opt = []; //使用此方法则无法获取到已有设置，只能重新设置了。
    }

    /**
     * 暂停或解除暂停当前会话
     *
     * 官方文档不齐全，不建议使用
     * @param int $bitmask 参数意义未知
     * @return int
     */
    public function pause($bitmask)
    {
        return curl_pause($this->handle, $bitmask);
    }

    /**
     * 重置当前会话的所有设置
     */
    public function reset()
    {
        curl_reset($this->handle);
        $this->opt = [];
    }

    /**
     * 为当前传输会话批量设置选项
     * @param array $options 要设置的选项数组
     * @return bool
     */
    public function setoptArray($options)
    {
        $rst = curl_setopt_array($this->handle, $options);
        if ($rst) {
            $this->opt = $options + $this->opt; //因为是数字键名，不能使用array_merge
        }
        return $rst;
    }

    /**
     * 为当前传输会话设置选项
     * @param int $option 需要设置的 CURLOPT_XXX 选项。
     * @param mixed $value 将设置在 option 选项上的值。
     * @return bool
     */
    public function setopt($option, $value)
    {
        if ($this->share) {
            $rst = curl_share_setopt($this->handle, $option, $value);
        } else {
            $rst = curl_setopt($this->handle, $option, $value);
        }
        if ($rst) {
            $this->opt[$option] = $value;
        }
        return $rst;
    }

    /**
     * 获取当前会话的所有设置选项
     * @return array
     */
    public function getopt()
    {
        return $this->opt;
    }

    /**
     * 根据错误码返回错误描述
     * @param int $errornum 返回的错误码
     * @return string
     */
    public static function strError($errornum)
    {
        return curl_strerror($errornum);
    }

    /**
     * 解码给定的 URL 编码的字符串
     * @param string $str 待解码字符串
     * @return string
     */
    public function unescape($str)
    {
        return curl_unescape($this->handle, $str);
    }

    /**
     * 获取 cURL 版本信息
     * @param int $age 参数意义未知
     * @return array
     */
    public static function version($age = 3)
    {
        return curl_version($age);
    }
}
