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

class SmsControllerTest extends \Orchestra\Testbench\TestCase
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

    public function testPostSendCode()
    {
        //1. test success mobile.
        $response = $this->post('sms/verify-code', ['mobile' => '18973305743']);

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => '短信发送成功']);

        //2. test repeat in 60 seconds.
        $response = $this->post('sms/verify-code', ['mobile' => '18973305743']);

        $response
            ->assertStatus(200)
            ->assertJson(['success' => false, 'message' => '每60秒发送一次']);

        //3. test invalid mobile.
        $response = $this->post('sms/verify-code', ['mobile' => '10000000000']);

        $response
            ->assertStatus(200)
            ->assertJson(['success' => false, 'message' => '无效手机号码']);
    }

    public function testInfo()
    {
        $response = $this->get('sms/info?mobile=18988885555');

        $response
            ->assertStatus(200);
    }
}
