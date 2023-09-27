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
            $result  = static::{$message['type']}($client_id, $message);
            if (!empty($result)) {
                $message['isRes'] = true;
                $message['result'] = $result;
                Gateway::sendToClient($client_id, json_encode($message));
            }
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

    public static function sendToCurrentClient($client_id, $message) {
        Gateway::sendToCurrentClient($message["msg"]);
    }

    public static function sendToGroup($client_id, $message) {
        Gateway::sendToGroup($message["group"], $message["msg"]);
    }

    public static function bindUid($client_id, $message) {
        Gateway::bindUid($client_id, $message["uid"]);
    }

    public static function closeClient($client_id, $message) {
        Gateway::closeClient($client_id);
    }

    /**
     * @throws \Exception
     */
    public static function closeCurrentClient($client_id, $message) {
        Gateway::closeCurrentClient($message["msg"]);
    }

    public static function destoryClient($client_id, $message) {
        Gateway::destoryClient($client_id);
    }

    public static function destoryCurrentClient($client_id, $message) {
        Gateway::destoryCurrentClient();
    }

    public static function joinGroup($client_id, $message) {
        Gateway::joinGroup($client_id, $message["group"]);
    }

    public static function leaveGroup($client_id, $message) {
        Gateway::leaveGroup($client_id, $message["group"]);
    }

    public static function getAllClientIdCount($client_id, $message) {
        return Gateway::getAllClientIdCount();
    }

    public static function getAllClientCount($client_id, $message) {
        return Gateway::getAllClientCount();
    }
}
