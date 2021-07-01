<?php

namespace fize\http;

/**
 * 共享 CURL
 */
class CurlShare extends Curl
{

    /**
     * 关闭当前会话
     */
    public function close()
    {
        curl_share_close($this->handle);
        $this->handle = null;
        $this->options = [];
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
     * @param string|null $url 指定链接 URL
     * @return resource
     */
    protected function init(string $url = null)
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
        $rst = curl_share_setopt($this->handle, $option, $value);
        if ($rst) {
            $this->options[$option] = $value;
        }
        return $rst;
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
}