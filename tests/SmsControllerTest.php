<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/2
 * Time: 14:29
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
        $response = $this->post('sms/verify-code',['mobile'=>'18988885555']);

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => '短信发送成功']);

        //2. test repeat in 60 seconds.

        $response = $this->post('sms/verify-code',['mobile'=>'18988885555']);

        $response
            ->assertStatus(200)
            ->assertJson(['success' => false, 'message' => '每60秒发送一次']);
    }

    public function testInfo()
    {
        $response= $this->get('sms/info?mobile=18988885555');

        $response
            ->assertStatus(200);
    }

}