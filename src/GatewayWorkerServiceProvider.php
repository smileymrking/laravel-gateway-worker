<?php

namespace LjhSmileKing\GatewayWorker;

use GatewayWorker\Lib\Gateway;
use Illuminate\Support\ServiceProvider;
use LjhSmileKing\GatewayWorker\Commands\GatewayWorkerCommand;

class GatewayWorkerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/gateway-worker.php' => config_path('gateway-worker.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/gateway-worker.php', 'gateway-worker');

        $this->app->bind('command.gateway-worker', GatewayWorkerCommand::class);

        $this->commands([
            'command.gateway-worker',
        ]);

        // WebSocket 注册地址
        $defaultService  = config('gateway-worker.default_service');
        Gateway::$registerAddress = config("gateway-worker.{$defaultService}.register_address");
    }
}
