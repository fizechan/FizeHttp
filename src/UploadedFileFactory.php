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
     * 从临时文件创建UploadedFile
     * @param array $value 上传文件数组
     * @return UploadedFile[]|UploadedFile
     */
    public function createUploadedFileFromSpec(array $value)
    {
        if (is_array($value['tmp_name'])) {
            return self::createUploadedFilesFromSpec($value);
        }
        return new UploadedFile(
            $value['tmp_name'],
            (int)$value['size'],
            (int)$value['error'],
            $value['name'],
            $value['type']
        );
    }

    /**
     * 从多个临时文件创建UploadedFile数组树
     * @param array $files
     * @return array
     */
    public function createUploadedFilesFromSpec(array $files = []): array
    {
        $uploaded_files = [];
        foreach (array_keys($files['tmp_name']) as $key) {
            $spec = [
                'tmp_name' => $files['tmp_name'][$key],
                'size'     => $files['size'][$key],
                'error'    => $files['error'][$key],
                'name'     => $files['name'][$key],
                'type'     => $files['type'][$key],
            ];
            $uploaded_files[$key] = $this->createUploadedFileFromSpec($spec);
        }
        return $uploaded_files;
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

    /**
     * 从全局变量创建UploadedFile对象数组
     * @param string|null $name 文件表单域名称，不指定则从所有文件中获取
     * @return UploadedFile[]
     */
    public function createUploadedFilesFromGlobals(string $name = null): array
    {
        $files = $name === null ? $_FILES : $_FILES[$name];
        if ($files === null) {
            throw new InvalidArgumentException(sprintf('Files "%s" does not exist.', $name));
        }
        if (!is_array($files)) {
            throw new InvalidArgumentException(sprintf('Files "%s" is not a array.', $name));
        }
        return $this->createUploadedFilesFromSpec($files);
    }

    /**
     * 设置服务端请求全局变量
     *
     * 本方法主要应用在模拟HTTP的单元测试中。
     */
    public function setGlobals(array $files, $format = false)
    {
        global $_FILES;
        if ($format) {
            $temp = [];
            $tmp_names = [];
            $sizes = [];
            $errors = [];
            $names = [];
            $types = [];
            foreach ($files as $key => $value) {
                $temp[$key] = [
                    'tmp_name' => $value['tmp_name'],
                    'size' => $value['size'],
                    'error' => $value['error'],
                    'name' => $value['name'],
                    'type' => $value['type']
                ];
            }
        }
        $_FILES = $files;  // @todo $_FILES不是规范化，需要转换。
    }

    public function setGlobalsByUploadedFiles(array $uploadedFiles)
    {
        global $_FILES;
        $_FILES = [];
    }
}
