<?php

/*
 * This file is part of ibrand/laravel-sms.
 *
 * (c) iBrand <https://www.ibrand.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace iBrand\Sms;

use Illuminate\Support\Collection;

/**
 * Class Code.
 */
class Code extends Collection
{
    /**
     * Code constructor.
     *
     * @param $to
     * @param $code
     * @param $sent
     * @param $attempts
     * @param $expireAt
     */
    public function __construct($to, $code, $sent, $attempts, $expireAt)
    {
        $items = compact('to', 'code', 'sent', 'attempts', 'expireAt');
        parent::__construct($items);
    }

    /**
     * Magic accessor.
     *
     * @param string $property property name
     *
     * @return mixed
     */
    public function __get($property)
    {
        if ($this->has($property)) {
            return $this->get($property);
        }
    }
}
