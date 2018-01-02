<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-12-28
 * Time: 12:55
 */

namespace Ibrand\Sms\Messages;


use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Message;
use Overtrue\EasySms\Contracts\GatewayInterface;
use Overtrue\EasySms\Strategies\OrderStrategy;

class CodeMessage extends Message
{
    protected $code;
    protected $minutes;

    public function __construct($code, $minutes)
    {
        $this->code = $code;
        $this->minutes = $minutes;
    }

    // 定义直接使用内容发送平台的内容
    public function getContent(GatewayInterface $gateway = null)
    {
        $content = config('ibrand.sms.content');

        return vsprintf($content, [$this->content, $this->minutes]);
    }

    // 定义使用模板发送方式平台所需要的模板 ID
    public function getTemplate(GatewayInterface $gateway = null)
    {
        $classname = get_class($gateway);

        if ($pos = strrpos($classname, '\\')) {
            $classname = substr($classname, $pos + 1);
        }

        if ($classname) {
            $classname = strtolower(str_replace('Gateway', '', $classname));
        }

        return config('ibrand.sms.easy_sms.gateways.' . $classname . '.code_template_id');

    }

    // 模板参数
    public function getData(GatewayInterface $gateway = null)
    {
        return [
            'code' => $this->code
        ];
    }

}