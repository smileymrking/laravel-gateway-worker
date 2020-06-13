<?php

namespace LjhSmileKing\GatewayWorker;

use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;

class GatewayWorkerService implements GatewayWorkerInterface
{
    use GatewayWorkerTrait;

    public function startBusinessWorker()
    {
        $worker                  = new BusinessWorker();
        $worker->name            = $this->config('worker_name');  #设置BusinessWorker进程的名称
        $worker->count           = $this->config('worker_count'); #设置BusinessWorker进程的数量
        $worker->registerAddress = $this->config('register_address'); #注册服务地址
        // 设置使用哪个类来处理业务,业务类至少要实现onMessage静态方法，onConnect和onClose静态方法可以不用实现
        $worker->eventHandler    = $this->config('event_handler', GatewayWorkerEvents::class);
    }

    public function startGateWay()
    {
        $gateway = new Gateway($this->config('gateway'), $this->config('gateway_context', []));  #连接服务的端口
        $gateway->transport = $this->config('gateway_transport', 'tcp');
        $gateway->name                 = $this->config('gateway_name');  #设置Gateway进程的名称，方便status命令中查看统计
        $gateway->count                = $this->config('gateway_count'); #进程的数量
        $gateway->lanIp                = $this->config('lan_ip');        #内网ip,多服务器分布式部署的时候需要填写真实的内网ip
        $gateway->startPort            = $this->config('start_port');    #监听本机端口的起始端口
        $gateway->pingInterval         = $this->config('ping_interval');
        $gateway->pingNotResponseLimit = $this->config('ping_not_response_limit'); # 0 服务端主动发送心跳, 1 客户端主动发送心跳
        $gateway->pingData             = $this->config('ping_data'); # 服务端主动发送心跳的数据
        $gateway->registerAddress      = $this->config('register_address'); #注册服务地址
    }

    public function startRegister()
    {
        new Register($this->config('register')); # 允许注册通讯的地址
    }
}
