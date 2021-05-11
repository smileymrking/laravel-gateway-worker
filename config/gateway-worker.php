<?php
/**
 * Created by PhpStorm.
 * User: King
 * Date: 2019/8/26
 * Time: 14:30
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Gateway Worker Service
    |--------------------------------------------------------------------------
    */

    'default_service' => 'push', # 默认的 Gateway::$registerAddress 设置为 push.register_address

    'push' => [
        'service' => \SmileyMrKing\GatewayWorker\GatewayWorker\Push\Push::class,
        'lan_ip' => env('WS_LAN_IP', '127.0.0.1'), #内网ip,多服务器分布式部署的时候需要填写真实的内网ip

        'register' => env('WS_REGISTER', 'text://0.0.0.0:20000'),
        'register_address' => env('WS_REGISTER_ADDRESS', '127.0.0.1:20000'), #注册服务地址

        'worker_name' => 'PushBusinessWorker', #设置 BusinessWorker 进程的名称
        'worker_count' => 1, #设置 BusinessWorker 进程的数量
        # 设置使用哪个类来处理业务,业务类至少要实现onMessage静态方法，onConnect 和 onClose 静态方法可以不用实现
        'event_handler' => \SmileyMrKing\GatewayWorker\GatewayWorker\Push\PushEvent::class,

        'gateway' => env('WS_GATEWAY', 'websocket://0.0.0.0:20010'),# 允许连接服务的地址
        'gateway_name' => 'PushGateway', #设置 Gateway 进程的名称，方便status命令中查看统计
        'gateway_count' => 1, # Gateway 进程的数量
        'start_port' => env('WS_START_PORT', '20100'),  #监听本机端口的起始端口
        'ping_interval' => 55,  # 心跳间隔时间，只针对服务端发送心跳
        'ping_not_response_limit' => 1,   # 0 服务端主动发送心跳, 1 客户端主动发送心跳
        'ping_data' => '{"type":"ping"}', # 服务端主动发送心跳的数据，只针对服务端发送心跳,客户端超时未发送心跳时会主动向客户端发送一次心跳检测

        'gateway_start' => true,
        'business_worker_start' => true,
        'register_start' => true,

        'gateway_transport' => 'tcp', // 当为 ssl 时，开启SSL，websocket+SSL 即 wss
        /*'gateway_context' => [
            // 更多ssl选项请参考手册 http://php.net/manual/zh/context.ssl.php
            'ssl' => array(
                // 请使用绝对路径
                'local_cert' => '/your/path/of/server.pem', // 也可以是crt文件
                'local_pk' => '/your/path/of/server.key',
                'verify_peer' => false,
                'allow_self_signed' => true, //如果是自签名证书需要开启此选项
            )
        ],*/
    ],

];
