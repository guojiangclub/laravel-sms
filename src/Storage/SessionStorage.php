<?php

/*
 * This file is part of ibrand/laravel-sms.
 *
 * (c) iBrand <https://www.ibrand.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace iBrand\Sms\Storage;

/**
 * Class SessionStorage
 * @package iBrand\Sms\Storage
 */
class SessionStorage implements StorageInterface
{
    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        session([
            $key => $value,
        ]);
    }

    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    public function get($key, $default)
    {
        return session($key, $default);
    }

    /**
     * @param $key
     */
    public function forget($key)
    {
        session()->forget($key);
    }
}
