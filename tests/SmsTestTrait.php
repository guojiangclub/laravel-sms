<?php

/*
 * This file is part of ibrand/laravel-sms.
 *
 * (c) iBrand <https://www.ibrand.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace iBrand\Sms\Test;

use DB;
use iBrand\Sms\Sms as SmsClass;
use iBrand\Sms\Storage\CacheStorage;
use Overtrue\EasySms\EasySms;
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

        //3. test use old code.
        $this->app['config']->set('ibrand.sms.code.maxAttempts', 1);
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

    public function testBadGateway()
    {
        //1. test does not exist gateway.
        $storage = config('ibrand.sms.storage', CacheStorage::class);

        $this->app['config']->set('ibrand.sms.easy_sms.default.gateways', ['bad_gateway']);

        $sms = new SmsClass(new EasySms(config('ibrand.sms.easy_sms')), new $storage());

        $result = $sms->send('18988888888');
        $this->assertFalse($result);
    }

    public function testSendUseDbLog()
    {
        $this->app['config']->set('ibrand.sms.dblog', true);

        $result = Sms::send('18988888888');
        $this->assertTrue($result);

        //check database
        $result = DB::table('laravel_sms_log')->where('mobile', '18988888888')->first();
        $this->assertNotNull($result);
        $this->assertEquals('18988888888', $result->mobile);
        $this->assertEquals(1, $result->is_sent);
    }


}
