<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-12-27
 * Time: 22:51
 */

namespace Ibrand\Sms\Storage;

interface StorageInterface
{
    public function set($key, $value);

    public function get($key, $default);

    public function forget($key);

}