<?php

/*
 * This file is part of ibrand/laravel-sms.
 *
 * (c) iBrand <https://www.ibrand.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace iBrand\Sms;

use iBrand\Sms\Storage\CacheStorage;
use Illuminate\Support\Facades\Route;
use Overtrue\EasySms\EasySms;
use iBrand\Sms\Http\Middleware\ThrottleRequests;

/**
 * Class ServiceProvider.
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
	/**
	 * @var string
	 */
	protected $namespace = 'iBrand\Sms';

	/**
	 * Boot the service provider.
	 */
	public function boot()
	{
		if ($this->app->runningInConsole()) {
			$this->publishes([
				__DIR__ . '/../config/config.php' => config_path('ibrand/sms.php'),
			]);

			$this->loadMigrationsFrom(__DIR__ . '/../migrations');
		}

		if (!$this->app->routesAreCached()) {
			$routeAttr = config('ibrand.sms.route', []);
			if (config('ibrand.sms.enable_rate_limit')) {
				$routeAttr['middleware'] = array_merge($routeAttr['middleware'], [config('ibrand.sms.rate_limit_middleware') . ':' . config('ibrand.sms.rate_limit_count') . ',' . config('ibrand.sms.rate_limit_time')]);
			}

			Route::group(array_merge(['namespace' => $this->namespace], $routeAttr), function ($router) {
				require __DIR__ . '/route.php';
			});
		}
	}

	/**
	 * Register the service provider.
	 */
	public function register()
	{
		$this->mergeConfigFrom(
			__DIR__ . '/../config/config.php', 'ibrand.sms'
		);

		$this->app->singleton(Sms::class, function ($app) {
			$storage = config('ibrand.sms.storage', CacheStorage::class);

			return new Sms(new EasySms(config('ibrand.sms.easy_sms')), new $storage());
		});
	}

	/**
	 * @return array
	 */
	public function provides()
	{
		return [Sms::class];
	}
}
