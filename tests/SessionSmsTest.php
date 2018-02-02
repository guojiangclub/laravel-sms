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
