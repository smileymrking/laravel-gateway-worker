<?php

namespace SmileyMrKing\GatewayWorker\GatewayWorker;

interface GatewayWorkerInterface
{
    public function start();

    public function ready();

    public function startBusinessWorker();

    public function startGateWay();

    public function startRegister();
}
