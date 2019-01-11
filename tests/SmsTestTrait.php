<?php

/*
 * This file is part of ibrand/laravel-sms.
 *
 * (c) iBrand <https://www.ibrand.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace iBrand\Sms\Test;

use DB;
use iBrand\Sms\Sms as SmsClass;
use iBrand\Sms\Storage\CacheStorage;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Gateways\YuntongxunGateway;
use Overtrue\EasySms\PhoneNumber;
use Overtrue\EasySms\Support\Config;
use Sms;

trait SmsTestTrait
{
	public function testStorage()
	{
		Sms::setStorage(new CacheStorage());

		$storage = Sms::getStorage();

		$this->assertEquals(CacheStorage::class, get_class($storage));
	}

	/**
	 * Test key.
	 */
	public function testKey()
	{
		$key = md5('ibrand.sms.18988888888');
		Sms::setKey('18988888888');
		$this->assertEquals($key, Sms::getKey());
	}

	/**
	 * Test send method.
	 */
	public function testSend()
	{
		//1. test send.
		$result = Sms::send('18988888888');
		$this->assertTrue($result);

		//2. test need create new code.
		$result = Sms::send('18988888888');
		$this->assertTrue($result);

		//3. test use old code.
		$this->app['config']->set('ibrand.sms.code.maxAttempts', 1);
		$result = Sms::send('18988888888');
		$this->assertTrue($result);
	}

	/**
	 * Test canSend method.
	 */
	public function testCanSend()
	{
		$result = Sms::canSend('18999999999');

		$this->assertTrue($result);

		Sms::send('18999999999');
		$result = Sms::canSend('18999999999');
		$this->assertFalse($result);
	}

	/**
	 * Test checkCode method.
	 */
	public function testCheckCode()
	{
		Sms::send('1897777777');

		$code = Sms::getCodeFromStorage();

		$result = Sms::checkCode('1897777777', $code->code);

		$this->assertTrue($result);

		Sms::send('1897777776');

		$code = Sms::getCodeFromStorage();

		$result = Sms::checkCode('1897777776', '12345');

		$this->assertFalse($result);
	}

	public function testBadGateway()
	{
		//1. test does not exist gateway.
		$storage = config('ibrand.sms.storage', CacheStorage::class);

		$this->app['config']->set('ibrand.sms.easy_sms.default.gateways', ['bad_gateway']);

		$sms = new SmsClass(new EasySms(config('ibrand.sms.easy_sms')), new $storage());

		$result = $sms->send('18988888888');
		$this->assertFalse($result);
	}

	public function testSendUseDbLog()
	{
		$this->app['config']->set('ibrand.sms.dblog', true);

		$result = Sms::send('18988888888');
		$this->assertTrue($result);

		//check database
		$result = DB::table('laravel_sms_log')->where('mobile', '18988888888')->first();
		$this->assertNotNull($result);
		$this->assertEquals('18988888888', $result->mobile);
		$this->assertEquals(1, $result->is_sent);
	}

	public function testSendByUserDefined()
	{
		$data = [
			'content'  => '【your app signature】亲爱的用户，您的验证码是%s。有效期为%s分钟，请尽快验证。',
			'template' => 'SMS_802xxx',
			'data'     => [
				'code' => mt_rand(10000, 99999),
			],
		];

		$result = Sms::send('18988888888', $data);
		$this->assertTrue($result);

		$gateways = ['xxx'];

		$result = Sms::send('18988888888', $data, $gateways);
		$this->assertFalse($result);

		$gateways = ['errorlog'];
		$result   = Sms::send('18988888888', $data, $gateways);
		$this->assertTrue($result);
	}

	public function testYunTongXun()
	{
		$yuntongxun = [
			'is_sub_account' => false,
			'account_sid'    => 'mock-account-sid',
			'account_token'  => 'mock-account-token',
			'app_id'         => 'mock-app-id',
		];
		$gateways   = config('ibrand.sms.easy_sms.gateways');
		$gateways   = array_merge($gateways, ['yuntongxun' => $yuntongxun]);
		config(['ibrand.sms.easy_sms.gateways' => $gateways]);

		$storage = config('ibrand.sms.storage', CacheStorage::class);
		$storage = new $storage();
		$easySms = new EasySms(config('ibrand.sms.easy_sms'));
		$sms     = new SmsClass($easySms, $storage);
		$code    = $sms->getNewCode('18188888888');

		$config  = [
			'debug'          => false,
			'is_sub_account' => false,
			'account_sid'    => 'mock-account-sid',
			'account_token'  => 'mock-account-token',
			'app_id'         => 'mock-app-id',
		];
		$gateway = \Mockery::mock(YuntongxunGateway::class . '[request]', [$config])->shouldAllowMockingProtectedMethods();
		$gateway->shouldReceive('request')->with(
			'post',
			\Mockery::on(function ($api) {
				return 0 === strpos($api, 'https://app.cloopen.com:8883/2013-12-26/Accounts/mock-account-sid/SMS/TemplateSMS?sig=');
			}),
			\Mockery::on(function ($params) use ($code) {
				return $params['json'] == [
						'to'         => '18188888888',
						'templateId' => 5589,
						'appId'      => 'mock-app-id',
						'datas'      => [$code->code, 'mock-data-2'],
					] && 'application/json' == $params['headers']['Accept']
					&& 'application/json;charset=utf-8' == $params['headers']['Content-Type'];
			})
		)->andReturn([
			'statusCode' => YuntongxunGateway::SUCCESS_CODE,
		], [
			'statusCode' => 100,
		])->once();

		$config  = new Config($config);
		$message = new CustomMessage($code->code);

		$this->assertSame([
			'statusCode' => YuntongxunGateway::SUCCESS_CODE,
		], $gateway->send(new PhoneNumber(18188888888), $message, $config));

		$smsMockery = \Mockery::mock(SmsClass::class . '[send]', [$easySms, $storage]);
		$smsMockery->shouldReceive('send')->with('18188888888', $message, ['yuntongxun'])->andReturn(
			true
		)->once();

		$result  = $smsMockery->send('18188888888', $message, ['yuntongxun']);
		$this->assertTrue($result);
	}
}
