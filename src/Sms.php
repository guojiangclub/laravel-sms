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

use Carbon\Carbon;
use iBrand\Sms\Messages\CodeMessage;
use iBrand\Sms\Storage\CacheStorage;
use iBrand\Sms\Storage\StorageInterface;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

/**
 * Class Sms
 * @package iBrand\Sms
 */
class Sms
{
    /**
     * @var EasySms
     */
    protected $easySms;
    /**
     * @var
     */
    protected $storage;

    /**
     * Sms constructor.
     * @param EasySms $easySms
     */
    public function __construct(EasySms $easySms)
    {
        $this->easySms = $easySms;
    }

    /**
     * @param StorageInterface $storage
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param $to
     *
     * @return bool
     */
    public function send($to)
    {
        //1. get code from storage.
        $code = $this->getCodeFromStorage($to);

        if ($this->needNewCode($code)) {
            $code = $this->getNewCode($to);
        }

        $validMinutes = (int) config('ibrand.sms.code.validMinutes', 5);

        $message = new CodeMessage($code->code, $validMinutes);

        try {
            $results = $this->easySms->send($to, $message);

            foreach ($results as $key => $value) {
                if ('success' == $value['status']) {
                    $code->put('sent', true);
                    $code->put('sentAt', Carbon::now());
                    $this->getStorage()->set('ibrand.sms.'.$to, $code);

                    return true;
                }
            }
        } catch (NoGatewayAvailableException $e) {
            return false;
        }

        return false;
    }

    /**
     * @param $to
     *
     * @return mixed
     */
    public function getCodeFromStorage($to)
    {
        return $this->getStorage()->get('ibrand.sms.'.$to, '');
    }

    /**
     * @param $code
     *
     * @return bool
     */
    public function needNewCode($code)
    {
        if (empty($code)) {
            return true;
        }

        $maxAttempts = config('ibrand.sms.code.maxAttempts');

        if ($code->expireAt > Carbon::now() and $code->attempts <= $maxAttempts) {
            return false;
        }

        return true;
    }

    /**
     * @param $to
     *
     * @return Code
     */
    public function getNewCode($to)
    {
        $code = $this->generateCode($to);

        $this->getStorage()->set('ibrand.sms.'.$to, $code);

        return $code;
    }

    /**
     * @param $to
     *
     * @return bool
     */
    public function canSend($to)
    {
        $code = $this->getStorage()->get('ibrand.sms.'.$to, '');

        if (empty($code) or $code->sentAt < Carbon::now()->addMinutes(-1)) {
            return true;
        }

        return false;
    }

    /**
     * @param $to
     *
     * @return Code
     */
    public function generateCode($to)
    {
        $length = (int) config('ibrand.sms.code.length', 5);
        $characters = '0123456789';
        $charLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[mt_rand(0, $charLength - 1)];
        }

        $validMinutes = (int) config('ibrand.sms.code.validMinutes', 5);

        return new Code($to, $randomString, false, 0, Carbon::now()->addMinutes($validMinutes));
    }

    /**
     * @return CacheStorage
     */
    public function getStorage()
    {
        return $this->storage ? $this->storage : new CacheStorage();
    }

    /**
     * @param $to
     * @param $inputCode
     *
     * @return bool
     */
    public function checkCode($to, $inputCode)
    {
        $code = $this->getStorage()->get('ibrand.sms.'.$to, '');

        if ($code and $code->code == $inputCode) {
            $this->getStorage()->forget('ibrand.sms.'.$to);

            return true;
        }

        $code->put('attempts', $code->attempts + 1);

        $this->getStorage()->set('ibrand.sms.'.$to, $code);

        return false;
    }
}
