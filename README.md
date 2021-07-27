# Laravel GatewayWorker
为了能够在 Laravel 中更优雅的使用 [GatewayWorker](https://github.com/walkor/GatewayWorker) 于是我基于 GatewayWorker 开发了这个扩展，使其能够开箱即用。

## 安装
```shell
composer require smileymrking/laravel-gateway-worker
```

## 配置

### Laravel

1. 在 `config/app.php` 注册 ServiceProvider 和 Facade (Laravel 5.5 + 无需手动注册)

```php
'providers' => [
    // ...
    SmileyMrKing\GatewayWorker\GatewayWorkerServiceProvider::class,
];
```

2. 创建配置文件：

```shell
php artisan vendor:publish --provider="SmileyMrKing\GatewayWorker\GatewayWorkerServiceProvider"
```

3. 修改应用根目录下的 `config/gateway-worker.php` 中对应的配置即可。

### Lumen
> 并未使用过 Lumen 未实际测试，以下参考其他扩展包编写

1. 在 `bootstrap/app.php` 中 82 行左右：

```php
$app->register(SmileyMrKing\GatewayWorker\GatewayWorkerServiceProvider::class);
```

2. 发布 `config/gateway-worker.php` 配置文件，将 `vendor/smileymrking/laravel-gateway-worker/config/gateway-worker.php` 拷贝到`项目根目录/config`目录下。


配置文件中已默认创建了一个名为 push 的 websocket 服务，配置如下，可自行调整相关配置，或无需发布配置文件，直接进入下一步启动服务
```php
return [

    /*
    |--------------------------------------------------------------------------
    | Gateway Worker Service
    |--------------------------------------------------------------------------
    */

    'default_service' => 'push', # 默认的 Gateway::$registerAddress 设置为 push.register_address

    'push' => [
        'service' => \SmileyMrKing\GatewayWorker\Push\Push::class,
        'lan_ip' => env('WS_LAN_IP', '127.0.0.1'), #内网ip,多服务器分布式部署的时候需要填写真实的内网ip

        'register' => env('WS_REGISTER', 'text://0.0.0.0:20000'),
        'register_address' => env('WS_REGISTER_ADDRESS', '127.0.0.1:20000'), #注册服务地址

        'worker_name' => 'PushBusinessWorker', #设置 BusinessWorker 进程的名称
        'worker_count' => 1, #设置 BusinessWorker 进程的数量
        # 设置使用哪个类来处理业务,业务类至少要实现onMessage静态方法，onConnect 和 onClose 静态方法可以不用实现
        'event_handler' => \SmileyMrKing\GatewayWorker\Push\PushEvent::class,

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

```

### 启动服务
使用以下命令启动服务  
`php artisan gateway-worker {serviceName} {action} {--d}`  

|参数|释义|
|:---:|:---|
|serviceName|服务名称，即配置文件中的键名|
|action|操作命令，可用命令有 `status` 、 `start` 、 `stop` 、 `restart` 、 `reload` 、 `connections`|
|--d|使用 DAEMON 模式|

```shell
> php artisan gateway-worker push start

Workerman[gateway-worker push] start in DEBUG mode
----------------------------------------------- WORKERMAN -----------------------------------------------
Workerman version:4.0.6          PHP version:7.2.5-1+ubuntu18.04.1+deb.sury.org+1
------------------------------------------------ WORKERS ------------------------------------------------
proto   user            worker                listen                       processes    status
tcp     vagrant         PushGateway           websocket://0.0.0.0:20010    1             [OK]
tcp     vagrant         PushBusinessWorker    none                         1             [OK]
tcp     vagrant         Register              text://0.0.0.0:20000         1             [OK]
---------------------------------------------------------------------------------------------------------
Press Ctrl+C to stop. Start success.
```  
> `push` 为默认创建的服务名称，可同步发布配置文件，自行修改相关配置

## 创建多个服务
> 可同时启动多个服务  

参考 `push` 服务，手动创建一个 Demao 类，继承 `SmileyMrKing\GatewayWorker\GatewayWorker\GatewayWorkerService`  
定义一个 `$serviceName` 属性，值为下一步所添加配置文的键名  
```php

namespace App\GatewayWorker\Demo;

use SmileyMrKing\GatewayWorker\GatewayWorker\GatewayWorkerService

class Demo extends GatewayWorkerService
{
    protected $serviceName = 'demo';
}

```

直接复制一份 push 的配置文件进行修改，注意需要修改 `worker_name` 、`gateway_name` 和相关端口的配置，避免重复  
添加配置的键名为 上一步定义的 `$serviceName` 的值，配置中 `service` 执行上一步配置的 Demo 类  

```php
return [
    // ...
    'demo' => [
        'service' => \App\GatewayWorker\Demo\Demo::class,
        'lan_ip' => env('WS_LAN_IP_DEMO', '127.0.0.1'), #内网ip,多服务器分布式部署的时候需要填写真实的内网ip

        'register' => env('WS_REGISTER_DEMO', 'text://0.0.0.0:20000'),
        'register_address' => env('WS_REGISTER_ADDRESS_DEMO', '127.0.0.1:20000'), #注册服务地址

        'worker_name' => 'DemoBusinessWorker', #设置 BusinessWorker 进程的名称
        'worker_count' => 1, #设置 BusinessWorker 进程的数量
        # 设置使用哪个类来处理业务,业务类至少要实现onMessage静态方法，onConnect 和 onClose 静态方法可以不用实现
        'event_handler' => \App\GatewayWorker\Demo\DemoEvent::class,

        'gateway' => env('WS_GATEWAY_DEMO', 'websocket://0.0.0.0:20010'),# 允许连接服务的地址
        'gateway_name' => 'DemoGateway', #设置 Gateway 进程的名称，方便status命令中查看统计
        'gateway_count' => 1, # Gateway 进程的数量
        'start_port' => env('WS_START_PORT_DEMO', '20100'),  #监听本机端口的起始端口
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

```


配置修改完成后使用 `php artisan gateway-worker demo start` 命令启动，`demo` 为刚刚配置的键名  
`event_handler` 未配置时默认使用 `SmileyMrKing\GatewayWorker\GatewayWorker\GatewayWorkerEvents` ，实现了 `onMessage` 、`onConnect`、`onClose` 三个静态方法  
可自定义 `event_handler` 类，需要继承 `SmileyMrKing\GatewayWorker\GatewayWorker\GatewayWorkerEvents` 然后重写相关静态方法
```php
namespace App\GatewayWorker\Demo;

use SmileyMrKing\GatewayWorker\GatewayWorkerEvents;

class DemoEvent extends GatewayWorkerEvents
{
    public static function onMessage($client_id, $message)
    {
        // Do something
    }
}
```

`default_service` 配置是指定 Gateway::$registerAddress 默认连接的哪个服务的注册地址 `register_address`  

## 消息推送
可直接使用 GatewayWorker 中的 `\GatewayWorker\Lib\Gateway` 类，具体用法请查看 [GatewayWorker 手册](http://doc2.workerman.net/)

## 日志查看
通过配置文件中 `service` 项所配置的类的命名空间查看  
如 `push` 服务的日志文件路径为：  
`vendor/smileymrking/laravel-gateway-worker/src/GatewayWorker/worker/smileymrking_gatewayworker_push_push.log`  
> 启动进程 pid 文件与日志文件路径和名称相同，后缀为 `.pid`

## 参考
- [GatewayWorker2.x 3.x 手册](http://doc2.workerman.net/)
- [在 Laravel 中使用 Workerman 进行 socket 通讯](https://learnku.com/articles/13151/using-laravel-to-carry-out-socket-communication-in-workerman)

## License

MIT
