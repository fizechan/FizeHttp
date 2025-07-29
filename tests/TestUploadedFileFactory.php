<?php

namespace Tests;

use Fize\Http\StreamFactory;
use Fize\Http\UploadedFileFactory;
use PHPUnit\Framework\TestCase;

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
        self::assertNotNull($upf);
    }

    public function testCreateUploadedFileFromSpec()
    {
        $spec = [
            'tmp_name' => __FILE__,
            'name' => basename(__FILE__),
            'size' => filesize(__FILE__),
            'type' => 'text/html',
            'error' => UPLOAD_ERR_OK,
        ];
        $factory = new UploadedFileFactory();
        $upf = $factory->createUploadedFileFromSpec($spec);
        var_dump($upf);
        self::assertNotNull($upf);
    }

    public function testCreateUploadedFilesFromSpec()
    {
        $files = [
            'file1' => [
                'tmp_name' => 'phpUxcOty',
                'name' => 'my-avatar.png',
                'size' => 90996,
                'type' => 'image/png',
                'error' => 0,
            ],

            'files1' => [
                'tmp_name' => [__FILE__, __FILE__],
                'name' => ['file0.txt', 'file1.html'],
                'size' => [filesize(__FILE__), filesize(__FILE__)],
                'type' => ['text/plain', 'text/html'],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
            ],

            'my-form' => [
                'details' => [
                    'file2' => [
                        'tmp_name' => 'phpUxcOty',
                        'name' => 'my-avatar.png',
                        'size' => 90996,
                        'type' => 'image/png',
                        'error' => 0,
                    ],
                ],
            ],

            'my-form2' => [
                'details2' => [
                    'files2' => [
                        'tmp_name' => [__FILE__, __FILE__],
                        'name' => ['file0.txt', 'file1.html'],
                        'size' => [filesize(__FILE__), filesize(__FILE__)],
                        'type' => ['text/plain', 'text/html'],
                        'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
                    ],
                ],
            ],
        ];
        $factory = new UploadedFileFactory();
        $upfs = $factory->createUploadedFilesFromSpec($files);
        var_dump($upfs);
        self::assertIsArray($upfs);
    }

    public function testCreateUploadedFileFromGlobals()
    {
        $files = [
            'file1' => [
                'tmp_name' => 'phpUxcOty',
                'name' => 'my-avatar.png',
                'size' => 90996,
                'type' => 'image/png',
                'error' => 0,
            ],

            'files1' => [
                'tmp_name' => [__FILE__, __FILE__],
                'name' => ['file0.txt', 'file1.html'],
                'size' => [filesize(__FILE__), filesize(__FILE__)],
                'type' => ['text/plain', 'text/html'],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
            ],

            'my-form' => [
                'details' => [
                    'file2' => [
                        'tmp_name' => 'phpUxcOty',
                        'name' => 'my-avatar.png',
                        'size' => 90996,
                        'type' => 'image/png',
                        'error' => 0,
                    ],
                ],
            ],

            'my-form2' => [
                'details2' => [
                    'files2' => [
                        'tmp_name' => [__FILE__, __FILE__],
                        'name' => ['file0.txt', 'file1.html'],
                        'size' => [filesize(__FILE__), filesize(__FILE__)],
                        'type' => ['text/plain', 'text/html'],
                        'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
                    ],
                ],
            ],
        ];
        UploadedFileFactory::setGlobals($files);
        $factory = new UploadedFileFactory();
        $upf = $factory->createUploadedFileFromGlobals('file1');
        var_dump($upf);
        self::assertNotNull($upf);
        $upf2 = $factory->createUploadedFileFromGlobals(['my-form', 'details', 'file2']);
        var_dump($upf2);
        self::assertNotNull($upf2);
    }

    public function testCreateUploadedFilesFromGlobals()
    {
        $files = [
            'file1' => [
                'tmp_name' => 'phpUxcOty',
                'name' => 'my-avatar.png',
                'size' => 90996,
                'type' => 'image/png',
                'error' => 0,
            ],

            'files1' => [
                'tmp_name' => [__FILE__, __FILE__],
                'name' => ['file0.txt', 'file1.html'],
                'size' => [filesize(__FILE__), filesize(__FILE__)],
                'type' => ['text/plain', 'text/html'],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
            ],

            'my-form' => [
                'details' => [
                    'file2' => [
                        'tmp_name' => 'phpUxcOty',
                        'name' => 'my-avatar.png',
                        'size' => 90996,
                        'type' => 'image/png',
                        'error' => 0,
                    ],
                ],
            ],

            'my-form2' => [
                'details2' => [
                    'files2' => [
                        'tmp_name' => [__FILE__, __FILE__],
                        'name' => ['file0.txt', 'file1.html'],
                        'size' => [filesize(__FILE__), filesize(__FILE__)],
                        'type' => ['text/plain', 'text/html'],
                        'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
                    ],
                ],
            ],
        ];
        UploadedFileFactory::setGlobals($files);
        $factory = new UploadedFileFactory();
        $upfs1 = $factory->createUploadedFilesFromGlobals();
        var_dump($upfs1);
        self::assertNotNull($upfs1);
        $upfs2 = $factory->createUploadedFilesFromGlobals('files1');
        var_dump($upfs2);
        self::assertNotNull($upfs2);
        $upfs3 = $factory->createUploadedFilesFromGlobals(['my-form2', 'details2', 'files2']);
        var_dump($upfs3);
        self::assertNotNull($upfs3);
    }

    public function testSetGlobals()
    {
        $files = [
            'file1' => [
                'tmp_name' => 'phpUxcOty',
                'name' => 'my-avatar.png',
                'size' => 90996,
                'type' => 'image/png',
                'error' => 0,
            ],

            'files1' => [
                'tmp_name' => [__FILE__, __FILE__],
                'name' => ['file0.txt', 'file1.html'],
                'size' => [filesize(__FILE__), filesize(__FILE__)],
                'type' => ['text/plain', 'text/html'],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
            ],

            'my-form' => [
                'details' => [
                    'file2' => [
                        'tmp_name' => 'phpUxcOty',
                        'name' => 'my-avatar.png',
                        'size' => 90996,
                        'type' => 'image/png',
                        'error' => 0,
                    ],
                ],
            ],

            'my-form2' => [
                'details2' => [
                    'files2' => [
                        'tmp_name' => [__FILE__, __FILE__],
                        'name' => ['file0.txt', 'file1.html'],
                        'size' => [filesize(__FILE__), filesize(__FILE__)],
                        'type' => ['text/plain', 'text/html'],
                        'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
                    ],
                ],
            ],
        ];
        UploadedFileFactory::setGlobals($files);
        var_dump($_FILES);
        self::assertNotNull($_FILES);

        $files = [
            'file1' => [
                'tmp_name' => 'phpUxcOty',
                'name' => 'my-avatar.png',
                'size' => 90996,
                'type' => 'image/png',
                'error' => 0,
            ],

            'files1' => [
                [
                    'tmp_name' => __FILE__,
                    'name' => 'file0.txt',
                    'size' => filesize(__FILE__),
                    'type' => 'text/plain',
                    'error' => UPLOAD_ERR_OK,
                ],
                [
                    'tmp_name' => __FILE__,
                    'name' => 'file1.html',
                    'size' => filesize(__FILE__),
                    'type' => 'text/html',
                    'error' => UPLOAD_ERR_OK,
                ]
            ],

            'my-form' => [
                'details' => [
                    'file2' => [
                        'tmp_name' => 'phpUxcOty',
                        'name' => 'my-avatar.png',
                        'size' => 90996,
                        'type' => 'image/png',
                        'error' => 0,
                    ],
                ],
            ],

            'my-form2' => [
                'details2' => [
                    'files2' => [
                        [
                            'tmp_name' => __FILE__,
                            'name' => 'file0.txt',
                            'size' => filesize(__FILE__),
                            'type' => 'text/plain',
                            'error' => UPLOAD_ERR_OK,
                        ],
                        [
                            'tmp_name' => __FILE__,
                            'name' => 'file1.html',
                            'size' => filesize(__FILE__),
                            'type' => 'text/html',
                            'error' => UPLOAD_ERR_OK,
                        ]
                    ],
                ],
            ],
        ];
        UploadedFileFactory::setGlobals($files, true);
        var_dump($_FILES);
        self::assertNotNull($_FILES);
    }

    public function testSetGlobalsByUploadedFiles()
    {
        $sfactory = new StreamFactory();
        $st = $sfactory->createStreamFromFile(__FILE__);
        $factory = new UploadedFileFactory();
        $clientFilename = basename(__FILE__);
        $clientMediaType = 'text/html';
        $upf = $factory->createUploadedFile($st, $st->getSize(), UPLOAD_ERR_OK, $clientFilename, $clientMediaType);
        $upFiles = [
            'file1' => $upf,
            'files1' => [
                $upf, $upf
            ],
            'my-form' => [
                'details' => [
                    'file2' => $upf
                ],
            ],
            'my-form2' => [
                'details2' => [
                    'files2' => [
                        $upf, $upf
                    ],
                ],
            ],
        ];
        UploadedFileFactory::setGlobalsByUploadedFiles($upFiles);
        var_dump($_FILES);
        self::assertNotNull($_FILES);

        $upFiles2 = [
            $upf, $upf, $upf
        ];
        UploadedFileFactory::setGlobalsByUploadedFiles($upFiles2, 'upfiles');
        var_dump($_FILES);
        self::assertNotNull($_FILES);
    }
}
