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

use iBrand\Sms\Storage\CacheStorage;

/**
 * Class SmsTest.
 */
class CacheSmsTest extends SmsTest
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('ibrand.sms.storage', CacheStorage::class);
    }
}
