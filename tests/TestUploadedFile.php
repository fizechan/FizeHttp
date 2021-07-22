<?php


use fize\http\UploadedFile;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class TestUploadedFile extends TestCase
{

    public function test__construct()
    {
        $upf = new UploadedFile(__FILE__, filesize(__FILE__), UPLOAD_ERR_OK);
        var_dump($upf);
        self::assertInstanceOf(UploadedFile::class, $upf);
    }

    public function testIsMoved()
    {
        $file = dirname(__DIR__) . '/temp/test.txt';
        $upf = new UploadedFile($file, filesize($file), UPLOAD_ERR_OK);
        $moved = $upf->isMoved();
        self::assertFalse($moved);
        $targetPath = dirname(__DIR__) . '/temp/uploaded.txt';
        $upf->moveTo($targetPath);
        $moved = $upf->isMoved();
        self::assertTrue($moved);
    }

    public function testGetStream()
    {
        $upf = new UploadedFile(__FILE__, filesize(__FILE__), UPLOAD_ERR_OK);
        $stream = $upf->getStream();
        var_dump($stream);
        self::assertInstanceOf(StreamInterface::class, $stream);
    }

    public function testMoveTo()
    {
        $file = dirname(__DIR__) . '/temp/test.txt';
        $upf = new UploadedFile($file, filesize($file), UPLOAD_ERR_OK);
        $moved = $upf->isMoved();
        self::assertFalse($moved);
        $targetPath = dirname(__DIR__) . '/temp/uploaded.txt';
        $upf->moveTo($targetPath);
        $moved = $upf->isMoved();
        self::assertTrue($moved);
    }

    public function testGetSize()
    {
        $upf = new UploadedFile(__FILE__, filesize(__FILE__), UPLOAD_ERR_OK);
        $size = $upf->getSize();
        var_dump($size);
        self::assertIsInt($size);
    }

    public function testGetError()
    {
        $upf = new UploadedFile(__FILE__, filesize(__FILE__), UPLOAD_ERR_EXTENSION);
        $error = $upf->getError();
        var_dump($error);
        self::assertEquals(UPLOAD_ERR_EXTENSION, $error);
    }

    public function testGetClientFilename()
    {
        $upf = new UploadedFile(__FILE__, filesize(__FILE__), UPLOAD_ERR_EXTENSION);
        $fname = $upf->getClientFilename();
        var_dump($fname);
        self::assertEquals(basename(__FILE__), $fname);
    }

    public function testGetClientMediaType()
    {
        $upf = new UploadedFile(__FILE__, filesize(__FILE__), UPLOAD_ERR_EXTENSION);
        $mime = $upf->getClientMediaType();
        var_dump($mime);
        self::assertEquals('text/html', $mime);
    }
}
