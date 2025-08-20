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
        ?int            $size = null,
        int             $error = UPLOAD_ERR_OK,
        ?string         $clientFilename = null,
        ?string         $clientMediaType = null
    ): UploadedFileInterface
    {
        return new UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }

    /**
     * 从临时文件创建UploadedFile
     * @param array $file 上传文件数组
     * @return UploadedFileInterface
     */
    public function createUploadedFileFromSpec(array $file): UploadedFileInterface
    {
        self::assertIsUploadItem($file);
        $upFile = new UploadedFile(
            $file['tmp_name'],
            (int)$file['size'],
            (int)$file['error'],
            $file['name'],
            $file['type']
        );
        if (isset($file['for_test']) && $file['for_test']) {
            $upFile->forTest();
        }
        return $upFile;
    }

    /**
     * 从多个临时文件创建UploadedFile数组树
     * @param array $files
     * @return array
     */
    public function createUploadedFilesFromSpec(array $files): array
    {
        $uploaded_files = [];
        if (self::isUploadItem($files)) {  // 自身已是上传文件对象
            if (is_array($files['tmp_name'])) {
                foreach ($files['tmp_name'] as $index => $tmp_name) {
                    $file = [
                        'tmp_name' => $files['tmp_name'][$index],
                        'name'     => $files['name'][$index],
                        'size'     => $files['size'][$index],
                        'type'     => $files['type'][$index],
                        'error'    => $files['error'][$index],
                        'for_test' => $files['for_test'][$index] ?? false
                    ];
                    $uploaded_files[] = $this->createUploadedFileFromSpec($file);
                }
            } else {
                $uploaded_files[] = $this->createUploadedFileFromSpec($files);
            }
            return $uploaded_files;
        }

        foreach ($files as $key => $item) {
            if (self::isUploadItem($item)) {
                if (is_array($item['tmp_name'])) {
                    $uploaded_files2 = [];
                    foreach ($item['tmp_name'] as $index => $tmp_name) {
                        $file = [
                            'tmp_name' => $item['tmp_name'][$index],
                            'name'     => $item['name'][$index],
                            'size'     => $item['size'][$index],
                            'type'     => $item['type'][$index],
                            'error'    => $item['error'][$index],
                            'for_test' => $item['for_test'][$index] ?? false
                        ];
                        $uploaded_files2[] = $this->createUploadedFileFromSpec($file);
                    }
                    $uploaded_files[$key] = $uploaded_files2;
                } else {
                    $uploaded_files[$key] = $this->createUploadedFileFromSpec($item);
                }
            } else {
                $uploaded_files[$key] = $this->createUploadedFilesFromSpec($item);
            }
        }
        return $uploaded_files;
    }

    /**
     * 从全局变量创建UploadedFile对象
     * @param string|array $name 文件表单域名称。如果是多维，则从顶部开始书写每层键名。
     * @return UploadedFileInterface
     */
    public function createUploadedFileFromGlobals($name): UploadedFileInterface
    {
        if (is_array($name)) {
            $file = $_FILES;
            foreach ($name as $key) {
                $file = $file[$key];
            }
        } else {
            $file = $_FILES[$name] ?? null;
        }
        if ($file === null) {
            throw new InvalidArgumentException(sprintf('File "%s" does not exist.', $name));
        }
        if (is_array($file['tmp_name'])) {
            throw new InvalidArgumentException(sprintf('File "%s" with multiple.', $name));
        }
        return $this->createUploadedFileFromSpec($file);
    }

    /**
     * 从全局变量创建UploadedFile对象数组
     * @param string|array|null $name 文件表单域名称。如果是多维，则从顶部开始书写每层键名。不指定则从所有文件中获取。
     * @return UploadedFile[]
     */
    public function createUploadedFilesFromGlobals($name = null): array
    {
        $files = $_FILES;
        if ($name) {
            if (is_array($name)) {
                $file = $_FILES;
                foreach ($name as $key) {
                    $file = $file[$key];
                }
            } else {
                $files = $_FILES[$name];
            }
        }
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
     * @param array $files  上传文件
     * @param bool  $format 是否按$_FILES格式进行转换
     * @return void
     */
    public static function setGlobals(array $files, bool $format = false)
    {
        global $_FILES;
        if ($format) {
            $files = self::convertToGlobalFiles($files);
        }
        $_FILES = $files;
    }

    /**
     * 设置服务端请求全局变量
     *
     * 本方法主要应用在模拟HTTP的单元测试中。
     * @param array $uploadedFiles 上传文件数组
     * @return void
     */
    public static function setGlobalsByUploadedFiles(array $uploadedFiles)
    {
        global $_FILES;
        $_FILES = self::convertToFilesArray($uploadedFiles);
    }

    /**
     * 将规范数组转化为$_FILES格式
     * @param array $files 规范数组
     * @return array
     */
    protected static function convertToGlobalFiles(array $files): array
    {
        $uploaded_files = [];
        foreach ($files as $key => $item) {
            if (self::isUploadItem($item)) {
                $uploaded_files[$key] = $item;
            } elseif (!self::isAssociativeArray($item)) {
                $tmp_names = [];
                $names = [];
                $sizes = [];
                $types = [];
                $errors = [];
                $for_tests = [];
                $isForTest = false;
                foreach ($item as $upFile) {
                    $tmp_names[] = $upFile['tmp_name'];
                    $names[] = $upFile['name'];
                    $sizes[] = $upFile['size'];
                    $types[] = $upFile['type'];
                    $errors[] = $upFile['error'];
                    $for_test = $upFile['for_test'] ?? false;
                    $for_tests[] = $for_test;
                    if ($for_test) {
                        $isForTest = true;
                    }
                }
                $item = [
                    'tmp_name' => $tmp_names,
                    'name'     => $names,
                    'size'     => $sizes,
                    'type'     => $types,
                    'error'    => $errors,
                ];
                if ($isForTest) {
                    $item['for_test'] = $for_tests;
                }
                $uploaded_files[$key] = $item;
            } else {
                $uploaded_files[$key] = self::convertToGlobalFiles($item);
            }
        }
        return $uploaded_files;
    }

    /**
     * 将 PSR-7 UploadedFile 对象或数组转换为 $_FILES 格式
     * @param array $uploadedFiles UploadedFile 数组
     * @return array 符合 $_FILES 结构的数组
     */
    protected static function convertToFilesArray(array $uploadedFiles): array
    {
        $files = [];
        // 遍历数组（字段名 => UploadedFile 对象或对象数组）
        foreach ($uploadedFiles as $field => $fileOrArray) {
            if (is_array($fileOrArray)) {
                if (self::isAssociativeArray($fileOrArray)) {
                    // 下级路径
                    $files[$field] = self::convertToFilesArray($fileOrArray);
                } else {
                    // 多文件上传（如：$files['documents'][0]）
                    $files[$field] = self::convertFileArray($fileOrArray);
                }
            } else {
                // 单文件上传（如：$files['avatar']）
                $files[$field] = self::convertFileSingle($fileOrArray);
            }
        }
        return $files;
    }

    /**
     * 转换单个 UploadedFile 对象为 $_FILES 单元
     * @param UploadedFile $file
     * @return array
     */
    protected static function convertFileSingle(UploadedFile $file): array
    {
        $upFile = [
            'tmp_name' => $file->getTmpName(),
            'name'     => $file->getClientFilename(),
            'size'     => $file->getSize(),
            'type'     => $file->getClientMediaType(),
            'error'    => $file->getError()
        ];
        if ($file->isForTest()) {
            $upFile['for_test'] = true;
        }
        return $upFile;
    }

    /**
     * 转换 UploadedFile 对象数组为 $_FILES 的多文件结构
     * @param UploadedFile[] $files
     * @return array
     */
    private static function convertFileArray(array $files): array
    {
        $result = ['tmp_name' => [], 'name' => [], 'size' => [], 'type' => [], 'error' => []];
        $for_tests = [];
        $isForTest = false;
        foreach ($files as $file) {
            $result['tmp_name'][] = $file->getTmpName();
            $result['name'][] = $file->getClientFilename();
            $result['size'][] = $file->getSize();
            $result['type'][] = $file->getClientMediaType();
            $result['error'][] = $file->getError();
            $for_test = $file->isForTest();
            $for_tests[] = $for_test;
            if ($for_test) {
                $isForTest = true;
            }
        }
        if ($isForTest) {
            $result['for_test'] = $for_tests;
        }
        return $result;
    }

    /**
     * 判断是否为上传文件
     * @param array $item 判断项
     * @return bool
     */
    protected static function isUploadItem(array $item): bool
    {
        $keys = ['tmp_name', 'name', 'size', 'type', 'error'];
        return count(array_intersect($keys, array_keys($item))) === count($keys);
    }

    /**
     * 检测上传文件
     * @param array $item 判断项
     */
    protected static function assertIsUploadItem(array $item)
    {
        if (!self::isUploadItem($item)) {
            throw new InvalidArgumentException("Item is not a valid upload item.");
        }
    }

    /**
     * 是否为关联数组
     * @param array $array
     * @return bool
     * @todo 考虑移除到外部。
     */
    protected static function isAssociativeArray(array $array): bool
    {
        foreach (array_keys($array) as $key) {
            // 如果有任何一个键不是整数，则是关联数组
            if (!is_int($key)) {
                return true;
            }
        }
        return false;
    }
}