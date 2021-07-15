<?php

use fize\http\CurlMulti;
use PHPUnit\Framework\TestCase;

class TestCurlMulti extends TestCase
{

    public function test__construct()
    {
        $cm = new CurlMulti();
        var_dump($cm);
        self::assertIsObject($cm);
    }

    public function test__destruct()
    {
        $cm = new CurlMulti();
        var_dump($cm);
        self::assertIsObject($cm);
        unset($cm);
    }

    public function testAddHandle()
    {
        $cm = new CurlMulti();
        $curl1 = curl_init();
        $rst = $cm->addHandle($curl1);
        var_dump($rst);
        self::assertIsInt($rst);
        self::assertEquals(0, $rst);
    }

    public function testClose()
    {
        $cm = new CurlMulti();
        $cm->close();
        var_dump($cm);
        self::assertIsObject($cm);
    }

    public function testExec()
    {
        $cm = new CurlMulti();
        $curl1 = curl_init('https://www.baidu.com');
        $cm->addHandle($curl1);
        $curl2 = curl_init('https://www.qq.com');
        $cm->addHandle($curl2);
        $still_running = 0;
        do {
            $mrc = $cm->exec($still_running);
            var_dump($still_running);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        self::assertEquals(CURLM_OK, $mrc);
    }

    public function testGetcontent()
    {
        $cm = new CurlMulti();
        $curl1 = curl_init('https://www.baidu.com/');
        curl_setopt($curl1, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl1, CURLOPT_HEADER, 0);
        $cm->addHandle($curl1);
        $curl2 = curl_init('https://www.qq.com/');
        curl_setopt($curl2, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl2, CURLOPT_HEADER, 0);
        $cm->addHandle($curl2);
        $still_running = 0;
        do {
            $mrc = $cm->exec($still_running);
            var_dump($still_running);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($still_running && $mrc == CURLM_OK) {
            if ($cm->select() != -1) {
                do {
                    $mrc = $cm->exec($still_running);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        $content = CurlMulti::getcontent($curl1);
        echo $content;
        self::assertIsString($content);
    }

    public function testInfoRead()
    {
        $urls = [
            "https://www.baidu.com/",
            "https://www.qq.com/",
            "http://www.yahoo.com/"
        ];

        $mh = new CurlMulti();

        foreach ($urls as $i => $url) {
            $conn[$i] = curl_init($url);
            curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER, 1);
            $mh->addHandle($conn[$i]);
        }

        do {
            $status = $mh->exec($active);
            $info = $mh->infoRead();
            if (false !== $info) {
                var_dump($info);
                self::assertIsArray($info);
            }
        } while ($status === CURLM_CALL_MULTI_PERFORM || $active);

        foreach ($urls as $i => $url) {
            $res[$i] = CurlMulti::getcontent($conn[$i]);
            curl_close($conn[$i]);
        }

        $info = $mh->infoRead();
        var_dump($info);
        self::assertFalse($info);
    }

    public function testRemoveHandle()
    {
        $cm = new CurlMulti();
        $curl1 = curl_init('https://www.baidu.com/');
        curl_setopt($curl1, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl1, CURLOPT_HEADER, 0);
        $cm->addHandle($curl1);
        $curl2 = curl_init('https://www.qq.com/');
        curl_setopt($curl2, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl2, CURLOPT_HEADER, 0);
        $cm->addHandle($curl2);
        $rst1 = $cm->removeHandle($curl2);
        var_dump($rst1);
        self::assertIsInt($rst1);
        $rst2 = $cm->removeHandle($curl2);
        var_dump($rst2);
        self::assertEquals(CURLM_OK, $rst2);
    }

    public function testSelect()
    {
        $cm = new CurlMulti();
        $curl1 = curl_init('https://www.baidu.com/');
        curl_setopt($curl1, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl1, CURLOPT_HEADER, 0);
        $cm->addHandle($curl1);
        $curl2 = curl_init('https://www.qq.com/');
        curl_setopt($curl2, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl2, CURLOPT_HEADER, 0);
        $cm->addHandle($curl2);
        $still_running = 0;
        do {
            $mrc = $cm->exec($still_running);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($still_running && $mrc == CURLM_OK) {
            $select = $cm->select();
            var_dump($select);
            self::assertIsInt($select);
            if ($select != -1) {
                do {
                    $mrc = $cm->exec($still_running);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
    }

    public function testSetopt()
    {
        $cm = new CurlMulti();
        $rst = $cm->setopt(CURLMOPT_MAXCONNECTS, 3);
        self::assertTrue($rst);
    }

    public function testStrerror()
    {
        $error = CurlMulti::strerror(CURLM_BAD_HANDLE);
        var_dump($error);
        self::assertIsString($error);
    }
}
