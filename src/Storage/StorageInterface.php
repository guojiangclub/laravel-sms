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
 * Interface StorageInterface.
 */
interface StorageInterface
{
    /**
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function set($key, $value);

    /**
     * @param $key
     * @param $default
     *
     * @return mixed
     */
    public function get($key, $default);

    /**
     * @param $key
     *
     * @return mixed
     */
    public function forget($key);
}
