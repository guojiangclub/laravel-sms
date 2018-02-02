<?php

namespace iBrand\Sms\Test;

use iBrand\Sms\Storage\CacheStorage;
use Sms;

trait SmsTestTrait
{

    public function testStorage()
    {
        Sms::setStorage(new CacheStorage());

        $storage = Sms::getStorage();

        $this->assertEquals(CacheStorage::class, get_class($storage));
    }

    /**
     * Test key.
     */
    public function testKey()
    {
        $key = md5('ibrand.sms.18988888888');
        Sms::setKey('18988888888');
        $this->assertEquals($key, Sms::getKey());
    }

    /**
     * Test send method.
     */
    public function testSend()
    {

        //1. test send.
        $result = Sms::send('18988888888');
        $this->assertTrue($result);

        //2. test need create new code.
        $result = Sms::send('18988888888');
        $this->assertTrue($result);
    }

    /**
     * Test canSend method.
     */
    public function testCanSend()
    {
        $result = Sms::canSend('18999999999');

        $this->assertTrue($result);

        Sms::send('18999999999');
        $result = Sms::canSend('18999999999');
        $this->assertFalse($result);
    }

    /**
     * Test checkCode method.
     */
    public function testCheckCode()
    {
        Sms::send('1897777777');

        $code = Sms::getCodeFromStorage();

        $result = Sms::checkCode('1897777777', $code->code);

        $this->assertTrue($result);

        Sms::send('1897777776');

        $code = Sms::getCodeFromStorage();

        $result = Sms::checkCode('1897777776', '12345');

        $this->assertFalse($result);
    }
}