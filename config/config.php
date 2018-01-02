<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-12-27
 * Time: 18:42
 */

return [


    'route' => [
        'prefix' => 'sms',
        'middleware' => ['web'],
    ],

    'easy_sms' => [

        'timeout' => 5.0,

        // 默认发送配置
        'default' => [
            // 网关调用策略，默认：顺序调用
            'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

            // 默认可用的发送网关
            'gateways' => [
                'aliyun',
            ],
        ],

        // 可用的网关配置
        'gateways' => [

            'errorlog' => [
                'file' => '/tmp/easy-sms.log',
            ],

            'yunpian' => [
                'api_key' => '824f0ff2f71cab52936axxxxxxxxxx',
            ],

            'aliyun' => [
                'access_key_id' => 'dalvT9gtAPdhHyk1',
                'access_key_secret' => 'xA7sKufp6WuwlQ5zcEfK7XV2TlC7jm',
                'sign_name' => '阿里云短信测试专用',
                'code_template_id' => 'SMS_80215252'
            ],

            'alidayu' => [
                //...
            ],
        ],
    ],

    'code' => [
        'length' => 5,
        'validMinutes' => 5,
        'maxAttempts' => 0
    ],

    'content' => '【your app signature】亲爱的用户，您的验证码是%s。有效期为%s分钟，请尽快验证。'
];