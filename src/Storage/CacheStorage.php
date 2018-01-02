<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-12-27
 * Time: 22:51
 */

namespace Ibrand\Sms\Storage;

use Cache;

class CacheStorage implements StorageInterface
{

    protected static $lifetime = 120;

    public static function setMinutesOfLifeTime($time)
    {
        if (is_int($time) && $time > 0) {
            self::$lifetime = $time;
        }
    }

    public function set($key, $value)
    {
        Cache::put($key, $value, self::$lifetime);
    }

    public function get($key, $default)
    {
        return Cache::get($key, $default);
    }

    public function forget($key)
    {
        if (Cache::has($key)) {
            Cache::forget($key);
        }
    }
}