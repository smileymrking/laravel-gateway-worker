> English｜中文
# Laravel GatewayWorker
In order to use [GatewayWorker](https://github.com/walkor/GatewayWorker) more elegantly in Laravel, I developed this extension based on GatewayWorker to make it ready to use.

## Installation
```shell
composer require smileymrking/laravel-gateway-worker
```

## Configuration

### Laravel

1. Register the ServiceProvider and Facade in `config/app.php` (For Laravel 5.5 and above, no manual registration is required)

```php
'providers' => [
// ...
SmileyMrKing\GatewayWorker\GatewayWorkerServiceProvider::class,
];
```

2. Publish the configuration file:

```shell
php artisan vendor:publish --provider="SmileyMrKing\GatewayWorker\GatewayWorkerServiceProvider"
```

3. Modify the corresponding configurations in `config/gateway-worker.php` located in the application root directory.

### Lumen
> Lumen has not been used or tested, the following instructions are based on other extension package development.

1. In `bootstrap/app.php` around line 82:

```php
$app->register(SmileyMrKing\GatewayWorker\GatewayWorkerServiceProvider::class);
```

2. Publish the `config/gateway-worker.php` configuration file by copying it from `vendor/smileymrking/laravel-gateway-worker/config/gateway-worker.php` to the `project_root/config` directory.

The configuration file already has a default websocket service named 'push', you can adjust the relevant configurations accordingly, or directly proceed to the next step to start the service.

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Gateway Worker Service
    |--------------------------------------------------------------------------
    */

    'default_service' => 'push', # Set Gateway::$registerAddress to push.register_address by default

    'push' => [
        'service' => \SmileyMrKing\GatewayWorker\Push\Push::class,
        'lan_ip' => env('WS_LAN_IP', '127.0.0.1'), # Internal IP, fill in the real internal IP when deploying in a multi-server distributed environment.

        'register' => env('WS_REGISTER', 'text://0.0.0.0:20000'),
        'register_address' => env('WS_REGISTER_ADDRESS', '127.0.0.1:20000'), # Registration service address

        'worker_name' => 'PushBusinessWorker', # Set the name of the BusinessWorker process
        'worker_count' => 1, # Set the number of BusinessWorker processes
        # Set which class to handle business logic. The class must implement the static method 'onMessage'. 'onConnect' and 'onClose' static methods are optional.
        'event_handler' => \SmileyMrKing\GatewayWorker\Push\PushEvent::class,

        'gateway' => env('WS_GATEWAY', 'websocket://0.0.0.0:20010'),# Address allowed for connection
        'gateway_name' => 'PushGateway', # Set the name of the Gateway process for easy statistics in the 'status' command
        'gateway_count' => 1, # Number of Gateway processes
        'start_port' => env('WS_START_PORT', '20100'),  # Starting port for listening on the local machine
        'ping_interval' => 55,  # Heartbeat interval, only for server-side heartbeat
        'ping_not_response_limit' => 1,   # 0: server actively sends heartbeat, 1: client actively sends heartbeat
        'ping_data' => '{"type":"ping"}', # Data for the server to actively send heartbeat, only for server-side heartbeat. When the client times out without sending heartbeat, the server will actively send a heartbeat detection.

        'gateway_start' => true,
        'business_worker_start' => true,
        'register_start' => true,

        'gateway_transport' => 'tcp', // When set to 'ssl', SSL will be enabled, websocket+SSL is 'wss'
        /*'gateway_context' => [
            // For more SSL options, please refer to the manual: http://php.net/manual/en/context.ssl.php
            'ssl' => array(
                // Please use absolute paths
                'local_cert' => '/your/path/of/server.pem', // It can also be a crt file
                'local_pk' => '/your/path/of/server.key',
                'verify_peer' => false,
                'allow_self_signed' => true, // Enable this option if it's a self-signed certificate
            )
        ],*/
    ],
    
    'pid_file' => null, // Custom PID file absolute path, by default in 'vendor/smileymrking/laravel-gateway-worker/src/GatewayWorker/worker' directory
    'log_file' => null, // Custom log file absolute path, same as above by default

];

```

### Starting the service
Use the following command to start the service:
`php artisan gateway-worker {serviceName} {action} {--d}`

| Parameter | Description |
|:---:|:---|
|serviceName|Service name, which is the key name in the configuration file.|
|action|Action command, available commands are `status`, `start`, `stop`, `restart`, `reload`, `connections`.|
|--d|Use DAEMON mode.|

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
> 'push' is the default service name that was created, you can synchronize the configuration file and modify the relevant configurations as needed.

## Creating Multiple Services
> You can start multiple services simultaneously.

#### Adding a new service
Refer to the 'push' service and manually create a 'Demo' class, which inherits from `SmileyMrKing\GatewayWorker\GatewayWorker\GatewayWorkerService`. Define a `$serviceName` property with the value of the key name to be added in the next step.

```php

namespace App\GatewayWorker\Demo;

use SmileyMrKing\GatewayWorker\GatewayWorker\GatewayWorkerService;

class Demo extends GatewayWorkerService
{
protected $serviceName = 'demo';
}

```

#### Adding Configuration
Copy and modify a copy of the push configuration file, making sure to modify the 'worker_name', 'gateway_name', and related port configurations to avoid duplication. The key name added to the configuration file should be the same as the value of the previously defined `$serviceName`, and set the 'service' in the configuration to the Demo class configured in the previous step.

```php
return [
// ...
'demo' => [
'service' => \App\GatewayWorker\Demo\Demo::class,
'lan_ip' => env('WS_LAN_IP_DEMO', '127.0.0.1'), # Internal IP, fill in the real internal IP when deploying in a multi-server distributed environment.

        'register' => env('WS_REGISTER_DEMO', 'text://0.0.0.0:20000'),
        'register_address' => env('WS_REGISTER_ADDRESS_DEMO', '127.0.0.1:20000'), # Registration service address

        'worker_name' => 'DemoBusinessWorker', # Set the name of the BusinessWorker process
        'worker_count' => 1, # Set the number of BusinessWorker processes
        # Set which class to handle business logic. The class must implement the static method 'onMessage'. 'onConnect' and 'onClose' static methods are optional.
        'event_handler' => \App\GatewayWorker\Demo\DemoEvent::class,

        'gateway' => env('WS_GATEWAY_DEMO', 'websocket://0.0.0.0:20010'),# Address allowed for connection
        'gateway_name' => 'DemoGateway', # Set the name of the Gateway process for easy statistics in the 'status' command
        'gateway_count' => 1, # Number of Gateway processes
        'start_port' => env('WS_START_PORT_DEMO', '20100'),  # Starting port for listening on the local machine
        'ping_interval' => 55,  # Heartbeat interval, only for server-side heartbeat
        'ping_not_response_limit' => 1,   # 0: server actively sends heartbeat, 1: client actively sends heartbeat
        'ping_data' => '{"type":"ping"}', # Data for the server to actively send heartbeat, only for server-side heartbeat. When the client times out without sending heartbeat, the server will actively send a heartbeat detection.

        'gateway_start' => true,
        'business_worker_start' => true,
        'register_start' => true,

        'gateway_transport' => 'tcp', // When set to 'ssl', SSL will be enabled, websocket+SSL is 'wss'
        /*'gateway_context' => [
            // For more SSL options, please refer to the manual: http://php.net/manual/en/context.ssl.php
            'ssl' => array(
                // Please use absolute paths
                'local_cert' => '/your/path/of/server.pem', // It can also be a crt file
                'local_pk' => '/your/path/of/server.key',
                'verify_peer' => false,
                'allow_self_signed' => true, // Enable this option if it's a self-signed certificate
            )
        ],*/
        'pid_file' => storage_path('logs/demo-gateway-worker.pid'),
        'log_file' => storage_path('logs/demo-gateway-worker.log'),
    ],

];

```


After completing the configuration modifications, use the `php artisan gateway-worker demo start` command to start the service, where 'demo' is the key name you configured.

#### Custom Event Handler
When 'event_handler' is not configured, it will use the `SmileyMrKing\GatewayWorker\GatewayWorker\GatewayWorkerEvents` class, which implements the static methods 'onMessage', 'onConnect', and 'onClose'. You can customize the 'event_handler' class by inheriting from `SmileyMrKing\GatewayWorker\GatewayWorker\GatewayWorkerEvents` and overriding the relevant static methods.

```php
namespace App\GatewayWorker\Demo;

use SmileyMrKing\GatewayWorker\GatewayWorker\GatewayWorkerEvents;

class DemoEvent extends GatewayWorkerEvents
{
public static function onMessage($client_id, $message)
{
// Do something
}
}
```

The 'default_service' configuration specifies which service's registration address Gateway::$registerAddress will connect to by default.

## Message Pushing
You can directly use the `\GatewayWorker\Lib\Gateway` class in GatewayWorker. For specific usage, please refer to the [GatewayWorker manual](http://doc2.workerman.net/).

## Viewing Logs
The logs and PID files are located in the `vendor/smileymrking/laravel-gateway-worker/src/GatewayWorker/worker` directory. You can customize the log and PID paths using the `pid_file` and `log_file` settings in the configuration.

## References
- [GatewayWorker 2.x 3.x Manual](http://doc2.workerman.net/)
- [Using Laravel to Carry Out Socket Communication in Workerman](https://learnku.com/articles/13151/using-laravel-to-carry-out-socket-communication-in-workerman)

## License

MIT
