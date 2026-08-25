<?php

declare(strict_types=1);

namespace Viciform\Laravel;

use Illuminate\Support\ServiceProvider;
use Viciform\Client;
use Viciform\Config;

class ViciformServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/viciform.php', 'viciform');

        $this->app->singleton(Client::class, function ($app) {
            $cfg = $app['config']->get('viciform', []);

            return new Client(new Config($cfg));
        });

        $this->app->alias(Client::class, 'viciform');
    }

    /**
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $configPath = function_exists('config_path')
                ? config_path('viciform.php')
                : $this->app->basePath('config/viciform.php');

            $this->publishes([
                __DIR__ . '/config/viciform.php' => $configPath,
            ], 'viciform-config');
        }
    }
}
