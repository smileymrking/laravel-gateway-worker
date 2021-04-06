<?php

namespace SmileyMrKing\GatewayWorker\Push;


use SmileyMrKing\GatewayWorker\GatewayWorkerEvents;

class PushEvent extends GatewayWorkerEvents
{
    public static function onMessage($client_id, $message)
    {
        // Do something
    }
}
