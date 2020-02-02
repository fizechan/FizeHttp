<?php


use fize\http\Client;
use PHPUnit\Framework\TestCase;

class TestClient extends TestCase
{

    public function test__construct()
    {
        $client = new Client();
        var_dump($client);
        self::assertIsObject($client);
    }

    public function testSendRequest()
    {

    }

    public function testAddOptions()
    {

    }





    public function testAddOption()
    {

    }
}
