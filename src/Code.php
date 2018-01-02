<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-12-28
 * Time: 0:41
 */

namespace Ibrand\Sms;


use Illuminate\Support\Collection;

class Code extends Collection
{

    public function __construct($to, $code, $sent, $attempts, $expireAt)
    {
        $items = compact('to', 'code', 'sent', 'attempts', 'expireAt');
        parent::__construct($items);
    }

    /**
     * Magic accessor.
     *
     * @param string $property Property name.
     *
     * @return mixed
     */
    public function __get($property)
    {
        if ($this->has($property)) {
            return $this->get($property);
        }

        return;
    }
}