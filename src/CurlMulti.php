<?php

namespace fize\http;

/**
 * 多进程 CURL
 */
class CurlMulti
{
    /**
     * @var resource 当前的 cURL 批处理句柄
     */
    private $mh;

    /**
     * 析构函数
     */
    public function __construct()
    {
        $this->mh = $this->init();
    }

    /**
     * 析构，关闭连接
     */
    public function __destruct()
    {
        if ($this->mh && get_resource_type($this->mh) == 'curl_multi') {
            $this->close();
        }
        $this->mh = null;
    }

    /**
     * 向当前批处理会话中添加一个 CURL 句柄
     * @param resource $ch 要添加进去的CURL句柄
     * @return int 错误码，0-成功
     */
    public function addHandle($ch): int
    {
        return curl_multi_add_handle($this->mh, $ch);
    }

    /**
     * 关闭当前批处理会话
     */
    public function close()
    {
        curl_multi_close($this->mh);
    }

    /**
     * 处理在栈中的每一个句柄
     * @param int|null $still_running 一个用来判断操作是否仍在执行的标识的引用。
     * @return int
     */
    public function exec(int &$still_running = null): int
    {
        return curl_multi_exec($this->mh, $still_running);
    }

    /**
     * 如果设置了 CURLOPT_RETURNTRANSFER ，则返回获取的输出的文本流
     * @param resource $ch Curl句柄，该句柄必须执行后才有输出
     * @return string
     */
    public static function getcontent($ch): string
    {
        return curl_multi_getcontent($ch);
    }

    /**
     * 获取当前解析的 cURL 的相关传输信息
     * @param int|null $msgs_in_queue 仍在队列中的消息数量。
     * @return array|false 失败时返回 false
     */
    public function infoRead(int &$msgs_in_queue = null)
    {
        return curl_multi_info_read($this->mh, $msgs_in_queue);
    }

    /**
     * 返回一个新 cURL 批处理句柄
     * @return resource
     */
    private function init()
    {
        return curl_multi_init();
    }

    /**
     * 移除 curl 批处理句柄资源中的某个句柄资源
     * @param resource $ch 要移除的Curl句柄
     * @return int|false 成功时返回一个 CURLM_XXX 错误码，失败时返回 FALSE 。
     */
    public function removeHandle($ch)
    {
        return curl_multi_remove_handle($this->mh, $ch);
    }

    /**
     * 等待所有 cURL 批处理中的活动连接,如果失败返回 -1
     * @param float $timeout 设定超时秒数
     * @return int
     */
    public function select(float $timeout = 1.0): int
    {
        return curl_multi_select($this->mh, $timeout);
    }

    /**
     * 为当前并行处理设置一个选项
     * @param int   $option 常量 CURLMOPT_* 之一。
     * @param mixed $value  将要设置给 option 的值。
     * @return bool
     */
    public function setopt(int $option, $value): bool
    {
        return curl_multi_setopt($this->mh, $option, $value);
    }

    /**
     * 根据错误码返回错误描述
     * @param int $errornum 返回的错误码
     * @return string
     */
    public static function strerror(int $errornum): string
    {
        return curl_multi_strerror($errornum);
    }
}
