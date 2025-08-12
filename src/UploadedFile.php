<?php

namespace Fize\Http;

use Fize\IO\File;
use Fize\Stream\Protocol\LazyOpenStream;
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
     * @var bool 是否为测试模式。测试模式下，原始文件不删除，以复制模式上传文件。
     */
    protected $forTest = false;

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

        $client_filename = $clientFilename;
        if (is_string($streamOrFile) && is_null($client_filename)) {
            $client_filename = basename($streamOrFile);
        }
        $this->setClientFilename($client_filename);

        $client_mime = $clientMediaType;
        if (is_string($streamOrFile) && is_null($client_mime)) {
            $client_mime = (new File($streamOrFile))->getMime();
        }
        $this->setClientMediaType($client_mime);

        if ($this->isUploadOK()) {
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
    public function getStream(): StreamInterface
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
    public function moveTo(string $targetPath): void
    {
        $this->validateActive();

        if (false === self::isStringNotEmpty($targetPath)) {
            throw new InvalidArgumentException(
                'Invalid path provided for move operation; must be a non-empty string'
            );
        }

        if ($this->file) {
            if ($this->forTest) {
                $this->moved = copy($this->file, $targetPath);  // 测试模式下，仅复制，不删除原文件。
            } else {
                $this->moved = php_sapi_name() == 'cli' ? rename($this->file, $targetPath) : move_uploaded_file($this->file, $targetPath);
            }
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
     * 获取临时文件路径（若文件已移动则返回空）
     * @return string
     */
    public function getTmpName(): string
    {
        $stream = $this->getStream();
        $metadata = $stream->getMetadata();
        return $metadata['uri'] ?? ''; // 返回流资源 URI（如 php://temp）
    }

    /**
     * 设置测试模式
     * @param bool $forTest true表示测试模式，false表示生产模式。
     * @return void
     */
    public function forTest(bool $forTest = true): void
    {
        $this->forTest = $forTest;
    }

    /**
     * 设置错误码
     * @param int $error 错误码
     * @throws InvalidArgumentException
     */
    private function setError(int $error)
    {
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
    private function isUploadOK(): bool
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
        if (false === $this->isUploadOK()) {
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
}
