<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/2
 * Time: 14:04
 */

namespace iBrand\Sms\Test;


use iBrand\Sms\Storage\SessionStorage;

class SessionSmsTest extends \Orchestra\Testbench\TestCase
{
    use SmsTestTrait;

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

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('ibrand.sms', require __DIR__.'/../config/config.php');
        $app['config']->set('ibrand.sms.storage', SessionStorage::class);
    }
}