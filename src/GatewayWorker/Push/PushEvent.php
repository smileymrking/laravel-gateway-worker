<?php

namespace SmileyMrKing\GatewayWorker\GatewayWorker\Push;


use GatewayWorker\Lib\Gateway;
use SmileyMrKing\GatewayWorker\GatewayWorker\GatewayWorkerEvents;

class PushEvent extends GatewayWorkerEvents
{
    public static function onMessage($client_id, $message)
    {
        $message = @json_decode($message, true);
        if ($message && !empty($message['type']) && method_exists(static::class, $message['type'])) {
            static::{$message['type']}($client_id, $message);
        } else {
            Gateway::sendToClient($client_id, json_encode(['type' => 'close']));
        }
    }
}
