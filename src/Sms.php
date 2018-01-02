<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-12-27
 * Time: 22:46
 */

namespace Ibrand\Sms;


use Carbon\Carbon;
use Ibrand\Sms\Messages\CodeMessage;
use Ibrand\Sms\Storage\CacheStorage;
use Ibrand\Sms\Storage\StorageInterface;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

class Sms
{
    protected $easySms;
    protected $storage;

    public function __construct(EasySms $easySms)
    {
        $this->easySms = $easySms;
    }

    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param $to
     * @return bool
     */
    public function send($to)
    {

        //1. get code from storage.
        $code = $this->getCodeFromStorage($to);

        if ($this->needNewCode($code)) {
            $code = $this->getNewCode($to);
        }

        $validMinutes = (int)config('ibrand.sms.code.validMinutes', 5);

        $message = new CodeMessage($code->code, $validMinutes);


        try {

            $results = $this->easySms->send($to, $message);

            foreach ($results as $key => $value) {
                if ($value['status'] == 'success') {
                    $code->put('sent', true);
                    $code->put('sentAt', Carbon::now());
                    $this->getStorage()->set('ibrand.sms.' . $to, $code);
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
     * @return mixed
     */
    public function getCodeFromStorage($to)
    {
        return $this->getStorage()->get('ibrand.sms.' . $to, '');
    }

    /**
     * @param $code
     * @return bool
     */
    public function needNewCode($code)
    {
        if (empty($code)) return true;

        $maxAttempts = config('ibrand.sms.code.maxAttempts');

        if ($code->expireAt > Carbon::now() AND  $code->attempts <= $maxAttempts) {
            return false;
        }

        return true;
    }

    /**
     * @param $to
     * @return Code
     */
    public function getNewCode($to)
    {
        $code = $this->generateCode($to);

        $this->getStorage()->set('ibrand.sms.' . $to, $code);

        return $code;
    }


    /**
     * @param $to
     * @return bool
     */
    public function canSend($to)
    {
        $code = $this->getStorage()->get('ibrand.sms.' . $to, '');

        if (empty($code) OR $code->sentAt < Carbon::now()->addMinutes(-1)) {
            return true;
        }

        return false;
    }

    /**
     * @param $to
     * @return Code
     */
    public function generateCode($to)
    {
        $length = (int)config('ibrand.sms.code.length', 5);
        $characters = '0123456789';
        $charLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[mt_rand(0, $charLength - 1)];
        }

        $validMinutes = (int)config('ibrand.sms.code.validMinutes', 5);

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
     * @return bool
     */
    public function checkCode($to, $inputCode)
    {
        $code = $this->getStorage()->get('ibrand.sms.' . $to, '');

        if ($code AND $code->code == $inputCode) {
            $this->getStorage()->forget('ibrand.sms.' . $to);
            return true;
        }

        $code->put('attempts', $code->attempts + 1);

        $this->getStorage()->set('ibrand.sms.' . $to, $code);

        return false;
    }
}