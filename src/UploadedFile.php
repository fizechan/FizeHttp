<?php

namespace fize\http;

use fize\stream\protocol\LazyOpenStream;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

/**
 * HTTP 文件上传
 */
class UploadedFile implements UploadedFileInterface
{

    /**
     * @var int[] 错误码
     */
    private static $errors = [
        UPLOAD_ERR_OK,
        UPLOAD_ERR_INI_SIZE,
        UPLOAD_ERR_FORM_SIZE,
        UPLOAD_ERR_PARTIAL,
        UPLOAD_ERR_NO_FILE,
        UPLOAD_ERR_NO_TMP_DIR,
        UPLOAD_ERR_CANT_WRITE,
        UPLOAD_ERR_EXTENSION,
    ];

    /**
     * @var int 错误码
     */
    private $error;

    /**
     * @var int 字节数
     */
    private $size;

    /**
     * @var string 客户端上传的文件名称
     */
    private $clientFilename;

    /**
     * @var string 客户端上传的文件类型
     */
    private $clientMediaType;

    /**
     * @var null|string 待处理文件
     */
    private $file;

    /**
     * @var StreamInterface|null 待处理数据流
     */
    private $stream;

    /**
     * @var bool 上传文件是否已移动
     */
    private $moved = false;

    /**
     * 构造
     * @param StreamInterface|string|resource $streamOrFile    文件或数据流
     * @param int                             $size            字节数
     * @param int                             $errorStatus     错误码
     * @param string|null                     $clientFilename  文件名称
     * @param string|null                     $clientMediaType 文件类型
     */
    public function __construct($streamOrFile, int $size, int $errorStatus, string $clientFilename = null, string $clientMediaType = null)
    {
        $this->setError($errorStatus);
        $this->setSize($size);

        if (is_string($streamOrFile) && is_null($clientFilename)) {
            $clientFilename = basename($streamOrFile);
        }
        $this->setClientFilename($clientFilename);

        if (is_string($streamOrFile) && is_null($clientMediaType)) {
            $clientMediaType = self::getMimeType($streamOrFile);
        }
        $this->setClientMediaType($clientMediaType);

        if ($this->isOk()) {
            $this->setStreamOrFile($streamOrFile);
        }
    }

    /**
     * 判断上传文件是否已移动
     * @return bool
     */
    public function isMoved(): bool
    {
        return $this->moved;
    }

    /**
     * 获取上传文件的数据流
     * @return StreamInterface
     */
    public function getStream()
    {
        $this->validateActive();

        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }

        return new LazyOpenStream($this->file, 'r+');
    }

    /**
     * 把上传的文件移动到新目录
     * @param string $targetPath 目标目录
     */
    public function moveTo($targetPath)
    {
        $this->validateActive();

        if (false === self::isStringNotEmpty($targetPath)) {
            throw new InvalidArgumentException(
                'Invalid path provided for move operation; must be a non-empty string'
            );
        }

        if ($this->file) {
            $this->moved = php_sapi_name() == 'cli' ? rename($this->file, $targetPath) : move_uploaded_file($this->file, $targetPath);
        } else {
            Stream::copyToStream($this->getStream(), new LazyOpenStream($targetPath, 'w'));
            $this->moved = true;
        }

        if (false === $this->moved) {
            throw new RuntimeException(
                sprintf('Uploaded file could not be moved to %s', $targetPath)
            );
        }
    }

    /**
     * 获取文件大小
     * @return int|null null表示未知
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * 获取上传文件时出现的错误码
     * @return int
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * 获取客户端上传的文件的名称
     * @return string|null null表示没有此值
     */
    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    /**
     * 客户端提交的文件类型
     *
     * 永远不要信任此方法返回的数据，客户端有可能发送了一个恶意的文件类型名称来攻击你的程序
     * @return string|null null表示没有此值
     */
    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }

    /**
     * 设置错误码
     * @param int $error 错误码
     * @throws InvalidArgumentException
     */
    private function setError(int $error)
    {
        if (false === is_int($error)) {
            throw new InvalidArgumentException(
                'Upload file error status must be an integer'
            );
        }

        if (false === in_array($error, self::$errors)) {
            throw new InvalidArgumentException(
                'Invalid error status for UploadedFile'
            );
        }

        $this->error = $error;
    }

    /**
     * 设置上传大小
     * @param int $size 字节数
     * @throws InvalidArgumentException
     */
    private function setSize(int $size)
    {
        if (false === is_int($size)) {
            throw new InvalidArgumentException(
                'Upload file size must be an integer'
            );
        }

        $this->size = $size;
    }

    /**
     * 设置客户端上传的文件的名称
     * @param string|null $clientFilename 文件名称
     * @throws InvalidArgumentException
     */
    private function setClientFilename(?string $clientFilename)
    {
        if (false === self::isStringOrNull($clientFilename)) {
            throw new InvalidArgumentException(
                'Upload file client filename must be a string or null'
            );
        }

        $this->clientFilename = $clientFilename;
    }

    /**
     * 设置客户端提交的文件类型
     * @param string|null $clientMediaType 文件类型
     */
    private function setClientMediaType(?string $clientMediaType)
    {
        if (false === self::isStringOrNull($clientMediaType)) {
            throw new InvalidArgumentException(
                'Upload file client media type must be a string or null'
            );
        }

        $this->clientMediaType = $clientMediaType;
    }

    /**
     * 判断上传是否无错误
     * @return bool
     */
    private function isOk(): bool
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    /**
     * 设置文件或数据流
     * @param StreamInterface|string|resource $streamOrFile 文件或数据流
     */
    private function setStreamOrFile($streamOrFile)
    {
        if (is_string($streamOrFile)) {
            $this->file = $streamOrFile;
        } elseif (is_resource($streamOrFile)) {
            $this->stream = new Stream($streamOrFile);
        } elseif ($streamOrFile instanceof StreamInterface) {
            $this->stream = $streamOrFile;
        } else {
            throw new InvalidArgumentException(
                'Invalid stream or file provided for UploadedFile'
            );
        }
    }

    /**
     * 检测是否已处理
     */
    private function validateActive()
    {
        if (false === $this->isOk()) {
            throw new RuntimeException('Cannot retrieve stream due to upload error');
        }

        if ($this->isMoved()) {
            throw new RuntimeException('Cannot retrieve stream after it has already been moved');
        }
    }

    /**
     * 判断为字符串或者null
     * @param mixed $param 带判断变量
     * @return bool
     */
    private static function isStringOrNull($param): bool
    {
        return in_array(gettype($param), ['string', 'NULL']);
    }

    /**
     * 判断为字符串并且不为空
     * @param mixed $param 带判断变量
     * @return bool
     */
    private static function isStringNotEmpty($param): bool
    {
        return is_string($param) && false === empty($param);
    }

    /**
     * 返回文件MIME
     * @param string $file_path 文件路径
     * @return string
     */
    private static function getMimeType(string $file_path): string
    {
        $mime_types = [  // 常见MIME
            'ai'   => 'application/postscript',
            'bmp'  => 'image/bmp',
            'cab'  => 'application/vnd.ms-cab-compressed',
            'css'  => 'text/css',
            'eps'  => 'application/postscript',
            'exe'  => 'application/x-msdownload',
            'doc'  => 'application/msword',
            'flv'  => 'video/x-flv',
            'gif'  => 'image/gif',
            'htm'  => 'text/html',
            'html' => 'text/html',
            'ico'  => 'image/vnd.microsoft.icon',
            'jpe'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg'  => 'image/jpeg',
            'js'   => 'application/javascript',
            'json' => 'application/json',
            'mov'  => 'video/quicktime',
            'mp3'  => 'audio/mpeg',
            'msi'  => 'application/x-msdownload',
            'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
            'odt'  => 'application/vnd.oasis.opendocument.text',
            'pdf'  => 'application/pdf',
            'php'  => 'text/html',
            'png'  => 'image/png',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'ps'   => 'application/postscript',
            'psd'  => 'image/vnd.adobe.photoshop',
            'qt'   => 'video/quicktime',
            'rar'  => 'application/x-rar-compressed',
            'rtf'  => 'application/rtf',
            'svg'  => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            'swf'  => 'application/x-shockwave-flash',
            'tif'  => 'image/tiff',
            'tiff' => 'image/tiff',
            'txt'  => 'text/plain',
            'xls'  => 'application/vnd.ms-excel',
            'xml'  => 'application/xml',
            'zip'  => 'application/zip',
        ];
        $temp = explode('.', $file_path);
        $ext = strtolower(array_pop($temp));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } elseif (extension_loaded('fileinfo')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimetype = finfo_file($finfo, $file_path);
            finfo_close($finfo);
            return $mimetype;
        } elseif (function_exists('mime_content_type')) {
            return mime_content_type($file_path);
        } else {
            return 'application/octet-stream';
        }
    }
}
