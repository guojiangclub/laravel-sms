<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-12-27
 * Time: 18:41
 */

namespace Ibrand\Sms;

use Ibrand\Sms\Storage\CacheStorage;
use Overtrue\EasySms\EasySms;
use Illuminate\Support\Facades\Route;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    //protected $defer = true;

    protected $namespace = 'Ibrand\Sms';

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('ibrand/sms.php'),
            ]);
        }

        if (!$this->app->routesAreCached()) {

            $routeAttr = config('ibrand.sms.route', []);


            Route::group(array_merge(['namespace' => $this->namespace], $routeAttr), function ($router) {
                require __DIR__ . '/route.php';
            });
        }

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php', 'ibrand.sms'
        );

        $this->app->singleton(Sms::class, function ($app) {
            return new Sms(new EasySms(config('ibrand.sms.easy_sms')));
        });
        //dd('111');
    }

    public function provides()
    {
        return [Sms::class];
    }
}