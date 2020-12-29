# Laravel Sms

Laravel 贴合实际需求同时满足多种通道的短信发送组件

[![Build Status](https://travis-ci.org/guojiangclub/laravel-sms.svg?branch=master)](https://travis-ci.org/guojiangclub/laravel-sms)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/guojiangclub/laravel-sms/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/guojiangclub/laravel-sms/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/guojiangclub/laravel-sms/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/guojiangclub/laravel-sms/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/guojiangclub/laravel-sms/badges/build.png?b=master)](https://scrutinizer-ci.com/g/guojiangclub/laravel-sms/build-status/master)
[![Latest Stable Version](https://poser.pugx.org/ibrand/laravel-sms/v/stable)](https://packagist.org/packages/ibrand/laravel-sms)
[![Latest Unstable Version](https://poser.pugx.org/ibrand/laravel-sms/v/unstable)](https://packagist.org/packages/ibrand/laravel-sms)
[![License](https://poser.pugx.org/ibrand/laravel-sms/license)](https://packagist.org/packages/ibrand/laravel-sms)

## Featrue

基于业务需求在 [overtrue/easy-sms][1] 基础进行扩展开发，主要实现如下目标：

1. 支持短信验证码直接在 config 中配置模板ID
2. 支持短信验证码自定义长度
3. 支持短信验证码有效分钟，默认5分钟
4. 支持短信验证码重试次数，防止用户意外输错验证码导致需要再次发送验证码的问题。
5. 支持短信验证码未验证时，用户再次请求验证码，在有效分钟内验证码保持一致。
6. 集成短信发送路由，支持 web 和 api 发送方式。
7. 支持验证码调试，debug 模式下可直接查询手机号目前有效的验证码
8. 支持短信验证码发送记录保存到数据库
9. 短信发送频率限制，同一 IP 限定时间内请求次数

## TODO：

1. 支持语音验证码

## 安装

```php
composer require ibrand/laravel-sms:~1.0 -vvv
```
## 发布
```shell
php artisan vendor:publish --provider='iBrand\Sms\ServiceProvider'
```

低于 Laravel5.5 版本

`config/app.php` 文件中 'providers' 添加
```php
iBrand\Sms\ServiceProvider::class
```

`config/app.php` 文件中 'aliases' 添加

```php
'Sms'=> iBrand\Sms\Facade::class
```

## 使用

### 发送验证码

1. 实现了发送短信验证码路由，支持 web 和 api ，可以自定义路由的 prefix。

```
'route' => [
        'prefix' => 'sms',
        'middleware' => ['web'],
    ],
    
or

'route' => [
        'prefix' => 'sms',
        'middleware' => ['api'],
    ],
```

POST请求 `http://your.domain/sms/verify-code` 

参数：mobile

备注：为了开发调试方便，在 debug 模式下不会验证手机的有效性。

返回参数：

```json
{
    "status": true,
    "message": "短信发送成功"
}
```

2. 如果需要自定义路由，也可以通过使用Facade发送验证码：

```php
use iBrand\Sms\Facade as Sms;

Sms::send(request('mobile'));
```

由于使用多网关发送，所以一条短信要支持多平台发送，每家的发送方式不一样，但是我们抽象定义了以下公用属性：

- `content` 文字内容，使用在像云片类似的以文字内容发送的平台
- `template` 模板 ID，使用在以模板ID来发送短信的平台
- `data` 模板变量，使用在以模板ID来发送短信的平台

```php
use iBrand\Sms\Facade as Sms;

Sms::send(request('mobile'), [
    'content'  => '您的验证码为: 83115',
    'template' => 'SMS_001',
    'data' => [
        'code' => 83115
    ],
]);
```

默认使用 `default` 中的设置来发送，如果某一条短信你想要覆盖默认的设置。在 `send` 方法中使用第三个参数即可：

```php
use iBrand\Sms\Facade as Sms;

Sms::send((request('mobile'), [
    'content'  => '您的验证码为: 83115',
    'template' => 'SMS_001',
    'data' => [
        'code' => 83115
    ],
], ['aliyun']); // 这里的网关配置将会覆盖全局默认
```

### 定义短信

已**容联云通讯**为例：你可以根据发送场景的不同，定义不同的短信类，通过继承 `Overtrue\EasySms\Message` 来定义短信模型：

```php
<?php
    
use Overtrue\EasySms\Message;
use Overtrue\EasySms\Contracts\GatewayInterface;

class CustomMessage extends Message
{
    protected $code;
    protected $gateways = ['yuntongxun']; //在sms.php配置文件中添加gateways选项: yuntongxun
    //...

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
        return 'templateId';
    }

    // 模板参数
    public function getData(GatewayInterface $gateway = null)
    {
        return [
            $this->code,
            //...
        ];    
    }
}
```

使用，具体请参考`iBrand\Sms\Test\CustomMessage`：

```php
use iBrand\Sms\Facade as Sms;
$code = Sms::getNewCode(request('mobile'));
$message = new CustomMessage($code->code);

Sms::send(request('mobile'), $message, ['yuntongxun']);
```

### 验证验证码

```php
use iBrand\Sms\Facade as Sms;

if (!Sms::checkCode(\request('mobile'), \request('code'))) {
    //Add you code.
}
```

### 配置模板 ID

在 `config/ibrand/sms.php` 的 `gateways` 参数可以直接添加 `code_template_id` 来配置模板 id

```php
    // 可用的网关配置
        'gateways' => [

            'errorlog' => [
                'file' => '/tmp/easy-sms.log',
            ],

            'yunpian' => [
                'api_key' => '824f0ff2f71cab52936axxxxxxxxxx',
            ],

            'aliyun' => [
                'access_key_id' => 'dalvTXXX',
                'access_key_secret' => 'XXXX',
                'sign_name' => '阿里云短信测试专用',
                'code_template_id' => 'SMS_80215252'
            ],

            'alidayu' => 
                //...
            ],
        ],
```

### 配置 Content

非模板类通道，可以通过 config/ibrand/sms.php 自定义短信内容

`'content' => '【your signature】亲爱的用户，您的验证码是%s。有效期为%s分钟，请尽快验证。'`

### debug 

在实际开发中会存在并不用真实发出验证码的情况，因此在 debug 模式下，可以通过

`http://your.domain/api/sms/info?mobile=1898888XXXX` 来直接只看某个手机号当前有效验证码信息。

### database log

目前已经支持把发送记录保存到数据库，执行 `php artisan migrate` 生成  `laravel_sms_log` 表。

同时在 `config/ibrand/sms.php` 把 `dblog` 设置为 `true`

```
'dblog' => true,
```


### Resources
1. [overtrue/easy-sms][1]
2. [toplan/laravel-sms][2]

  [1]: https://github.com/overtrue/easy-sms/
  [2]: https://github.com/toplan/laravel-sms/
