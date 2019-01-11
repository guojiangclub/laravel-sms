<?php

namespace iBrand\Sms\Test;

use Overtrue\EasySms\Message;
use Overtrue\EasySms\Contracts\GatewayInterface;

class CustomMessage extends Message
{
	protected $code;
	protected $gateways = ['yuntongxun'];

	public function __construct($code)
	{
		$this->code = $code;
	}

	// 定义直接使用内容发送平台的内容
	public function getContent(GatewayInterface $gateway = null)
	{

	}

	// 定义使用模板发送方式平台所需要的模板 ID
	public function getTemplate(GatewayInterface $gateway = null)
	{
		return '5589';
	}

	// 模板参数
	public function getData(GatewayInterface $gateway = null)
	{
		return [
			$this->code,
			'mock-data-2',
		];
	}
}