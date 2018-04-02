<?php

/*
 * This file is part of ibrand/laravel-sms.
 *
 * (c) iBrand <https://www.ibrand.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace iBrand\Sms\Messages;

use Overtrue\EasySms\Contracts\GatewayInterface;
use Overtrue\EasySms\Message;

/**
 * Class CodeMessage.
 */
class CodeMessage extends Message
{
    /**
     * @var
     */
    protected $code;
    /**
     * @var
     */
    protected $minutes;

    /**
     * CodeMessage constructor.
     *
     * @param $code
     * @param $minutes
     */
    public function __construct($code, $minutes)
    {
        $this->code = $code;
        $this->minutes = $minutes;
    }

    /**
     * 定义直接使用内容发送平台的内容.
     *
     * @param GatewayInterface|null $gateway
     *
     * @return string
     */
    public function getContent(GatewayInterface $gateway = null)
    {
        $content = config('ibrand.sms.content');

        return vsprintf($content, [$this->content, $this->minutes]);
    }

    /**
     * 定义使用模板发送方式平台所需要的模板 ID.
     *
     * @param GatewayInterface|null $gateway
     *
     * @return mixed
     */
    public function getTemplate(GatewayInterface $gateway = null)
    {
        $classname = get_class($gateway);

        if ($pos = strrpos($classname, '\\')) {
            $classname = substr($classname, $pos + 1);
        }

        if ($classname) {
            $classname = strtolower(str_replace('Gateway', '', $classname));
        }

        return config('ibrand.sms.easy_sms.gateways.'.$classname.'.code_template_id');
    }

    /**
     * @param GatewayInterface|null $gateway
     *
     * @return array
     */
    public function getData(GatewayInterface $gateway = null)
    {
        return array_merge(['code' => $this->code],config('ibrand.sms.data'));
    }
}
