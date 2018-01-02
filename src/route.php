<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-12-27
 * Time: 23:03
 */


Route::post('verify-code', 'SmsController@postSendCode');

Route::get('info', 'SmsController@info');