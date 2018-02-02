<?php

/*
 * This file is part of ibrand/laravel-sms.
 *
 * (c) iBrand <https://www.ibrand.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Class SmsTest.
 */
class SmsTest extends Orchestra\Testbench\TestCase
{
    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['iBrand\Sms\ServiceProvider'];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Sms' => "iBrand\Sms\Facade",
        ];
    }

    /**
     * Test key.
     */
    public function testKey()
    {
        $key = md5('ibrand.sms.18988888888');
        Sms::setKey('18988888888');
        $this->assertSame($key, Sms::getKey());
    }

    /**
     * Test send method.
     */
    public function testSend()
    {
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
