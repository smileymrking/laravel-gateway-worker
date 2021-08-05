<?php

namespace SmileyMrKing\GatewayWorker\GatewayWorker;

trait GatewayWorkerTrait
{
    protected $serviceName = null;

    public function config($name, $default = null)
    {
        return config("gateway-worker." . $this->serviceName . ".{$name}", $default);
    }
}
