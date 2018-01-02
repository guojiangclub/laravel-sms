<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-12-27
 * Time: 22:51
 */

namespace Ibrand\Sms\Storage;

class SessionStorage implements StorageInterface
{

    public function set($key, $value)
    {
        session([
            $key => $value,
        ]);
    }

    public function get($key, $default)
    {
        return session($key, $default);
    }

    public function forget($key)
    {
        session()->forget($key);
    }
}