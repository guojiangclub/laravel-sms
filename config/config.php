<?php

/*
 * This file is part of ibrand/laravel-sms.
 *
 * (c) iBrand <https://www.ibrand.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
	'route' => [
		'prefix'     => 'sms',
		'middleware' => ['web'],
	],

	'easy_sms' => [
		'timeout'  => 5.0,

		// 默认发送配置
		'default'  => [
			// 网关调用策略，默认：顺序调用
			'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

			// 默认可用的发送网关
			'gateways' => [
				'errorlog',
			],
		],

		// 可用的网关配置
		'gateways' => [
			'errorlog' => [
				'file' => storage_path('logs/laravel-sms.log'),
			],

			'yunpian' => [
				'api_key' => '824f0ff2f71cab52936axxxxxxxxxx',
			],

			'aliyun' => [
				'access_key_id'     => 'xxxx',
				'access_key_secret' => 'xxxx',
				'sign_name'         => '阿里云短信测试专用',
				'code_template_id'  => 'SMS_802xxx',
			],

			'alidayu' => [
				//...
			],
		],
	],

	'code' => [
		'length'       => 5,
		'validMinutes' => 5,
		'maxAttempts'  => 0,
	],

	'data' => [
		'product' => '',
	],

	'dblog' => false,

	'content' => '【your app signature】亲爱的用户，您的验证码是%s。有效期为%s分钟，请尽快验证。',

	'storage' => \iBrand\Sms\Storage\CacheStorage::class,

	'enable_rate_limit' => env('SMS_ENABLE_RATE_LIMIT', false),//是否开启

	'rate_limit_middleware' => 'iBrand\Sms\Http\Middleware\ThrottleRequests',

	'rate_limit_count' => env('SMS_RATE_LIMIT_COUNT', 10), //次数

	'rate_limit_time' => env('SMS_RATE_LIMIT_TIME', 60), //秒
];
