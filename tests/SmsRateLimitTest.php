<?php

namespace iBrand\Sms\Test;

class SmsRateLimitTest extends SmsTest
{
	public function testRateLimit()
	{
		//1. test success mobile.
		$response = $this->post('sms/verify-code', ['mobile' => '18973305743']);

		$response
			->assertStatus(200)
			->assertJson(['success' => true, 'message' => '短信发送成功']);

		//2. test repeat in 60 seconds.
		$response = $this->post('sms/verify-code', ['mobile' => '18973305743']);

		$response
			->assertStatus(200)
			->assertJson(['success' => false, 'message' => '每60秒发送一次']);

		//3. test invalid mobile.
		$response = $this->post('sms/verify-code', ['mobile' => '10000000000']);

		$response
			->assertStatus(200)
			->assertJson(['success' => false, 'message' => '无效手机号码']);

		//3. test invalid mobile.
		$response = $this->post('sms/verify-code', ['mobile' => '10000000000']);
		$response->assertStatus(429);
		$this->assertSame($response->getContent(), json_encode(["message" => "Too many attempts, please slow down the request.", "status_code" => 429]));
	}
}