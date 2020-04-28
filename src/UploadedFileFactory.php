<?php

namespace fize\http;

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
    public function createUploadedFile(StreamInterface $stream, int $size = null, int $error = UPLOAD_ERR_OK, string $clientFilename = null, string $clientMediaType = null): UploadedFileInterface
    {
        return new UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }
}
