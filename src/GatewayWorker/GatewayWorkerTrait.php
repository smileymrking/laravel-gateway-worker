<?php

namespace SmileyMrKing\GatewayWorker;

use Workerman\Worker;

trait GatewayWorkerTrait
{
    public function start()
    {
        if ($this->config('gateway_start')) $this->startGateWay();
        if ($this->config('business_worker_start')) $this->startBusinessWorker();
        if ($this->config('register_start')) $this->startRegister();

        $path = __DIR__ . '/worker';
        if (!is_dir($path)) mkdir($path);
        $unique_prefix = \str_replace('\\', '_', strtolower(static::class));
        Worker::$pidFile = "$path/$unique_prefix.pid";
        Worker::$logFile = "$path/$unique_prefix.log";
        Worker::runAll();
    }

    public function config($name, $default = null)
    {
        return config("gateway-worker.{$this->serviceName}.$name", $default);
    }
}
