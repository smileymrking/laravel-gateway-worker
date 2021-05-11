<?php

namespace SmileyMrKing\GatewayWorker\GatewayWorker\Push;


use SmileyMrKing\GatewayWorker\GatewayWorker\GatewayWorkerEvents;

class PushEvent extends GatewayWorkerEvents
{
    public static function onMessage($client_id, $message)
    {
        // Do something
    }
}
