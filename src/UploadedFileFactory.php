<?php

namespace Fize\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * HTTP文件上传工厂类
 */
class UploadedFileFactory implements UploadedFileFactoryInterface
{

    /**
     * 创建一个上传文件接口的对象
     * @param StreamInterface $stream          数据流
     * @param int|null        $size            字节数
     * @param int             $error           错误码
     * @param string|null     $clientFilename  文件名称
     * @param string|null     $clientMediaType 文件类型
     * @return UploadedFileInterface
     */
    public function createUploadedFile(
        StreamInterface $stream,
        int             $size = null,
        int             $error = UPLOAD_ERR_OK,
        string          $clientFilename = null,
        string          $clientMediaType = null
    ): UploadedFileInterface
    {
        return new UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }

    /**
     * 从全局变量创建UploadedFile对象
     * @param string $name 文件表单域名称
     * @return UploadedFileInterface
     */
    public function createUploadedFileFromGlobals(string $name): UploadedFileInterface
    {
        $file = $_FILES[$name] ?? null;
        if ($file === null) {
            throw new InvalidArgumentException(sprintf('File "%s" does not exist.', $name));
        }
        if (is_array($file['tmp_name'])) {
            throw new InvalidArgumentException(sprintf('File "%s" with multiple.', $name));
        }
        return new UploadedFile(
            $file['tmp_name'],
            (int)$file['size'],
            (int)$file['error'],
            $file['name'],
            $file['type']
        );
    }

    public function createUploadedFilesFromGlobals($name = null): array
    {
        $files = $name === null ? $_FILES : $_FILES[$name];
        if ($files === null) {
            throw new InvalidArgumentException(sprintf('Files "%s" does not exist.', $name));
        }
        if (!is_array($files)) {
            throw new InvalidArgumentException(sprintf('Files "%s" is not a array.', $name));
        }
        $upfiles = [];
    }

    /**
     * 设置服务端请求全局变量
     *
     * 本方法主要应用在模拟HTTP的单元测试中。
     */
    public function setGlobals(array $files, $format = false)
    {
        global $_FILES;
        $_FILES = $files;  // @todo $_FILES不是规范化，需要转换。
    }
}
