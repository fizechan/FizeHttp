<?php

namespace Tests;

use Fize\Http\StreamFactory;
use Fize\Http\UploadedFileFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

class TestUploadedFileFactory extends TestCase
{

    public function testCreateUploadedFile()
    {
        $sfactory = new StreamFactory();
        $st = $sfactory->createStreamFromFile(__FILE__);
        $factory = new UploadedFileFactory();
        $clientFilename = basename(__FILE__);
        $clientMediaType = 'text/html';
        $upf = $factory->createUploadedFile($st, $st->getSize(), UPLOAD_ERR_OK, $clientFilename, $clientMediaType);
        var_dump($upf);
        self::assertInstanceOf(UploadedFileInterface::class, $upf);
    }
}
