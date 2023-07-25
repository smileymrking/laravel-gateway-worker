<?php

namespace SmileyMrKing\GatewayWorker\GatewayWorker;

interface GatewayWorkerInterface
{
    public static function startAll($serviceName);

    public function ready($serviceName);

    public function startBusinessWorker();

    public function startGateWay();

    public function startRegister();
}
