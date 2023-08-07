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

    public static function sendToAll($client_id, $message) {
        Gateway::sendToAll($message["msg"]);
    }

    public static function sendToClient($client_id, $message) {
        Gateway::sendToClient($client_id, $message["msg"]);
    }

    public static function sendToUid($client_id, $message) {
        Gateway::sendToUid($message["uid"], $message["msg"]);
    }
}
