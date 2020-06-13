<?php

namespace LjhSmileKing\GatewayWorker\Push;


use LjhSmileKing\GatewayWorker\GatewayWorkerEvents;

class PushEvent extends GatewayWorkerEvents
{
    public static function onMessage($client_id, $message)
    {
        // Do something
    }
}