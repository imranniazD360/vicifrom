<?php

declare(strict_types=1);

namespace Viciform\Laravel;

use Illuminate\Support\ServiceProvider;
use Viciform\Client;
use Viciform\Config;
use Viciform\Laravel\Commands\ConfigureCommand;
use Viciform\Laravel\Commands\InstallCommand;
use Viciform\Laravel\Commands\StatusCommand;
use Viciform\Laravel\Commands\TestCommand;

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

        $this->app->singleton(ViciformManager::class, function ($app) {
            return new ViciformManager($app->make(Client::class));
        });

        $this->app->alias(ViciformManager::class, 'viciform');
        $this->app->alias(Client::class, 'viciform.client');
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

            $this->publishes([
                __DIR__ . '/stubs/env.viciform.stub' => $this->app->basePath('viciform.env.example'),
            ], 'viciform-env');

            $this->commands([
                InstallCommand::class,
                ConfigureCommand::class,
                StatusCommand::class,
                TestCommand::class,
            ]);
        }
    }
}
