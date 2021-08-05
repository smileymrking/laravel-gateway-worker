<?php

namespace SmileyMrKing\GatewayWorker\Commands;

use Illuminate\Console\Command;
use SmileyMrKing\GatewayWorker\GatewayWorker\GatewayWorkerTrait;

class GatewayWorkerCommand extends Command
{
    use GatewayWorkerTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gateway-worker {serviceName} {action} {--d}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start a GatewayWorker Service.';


    protected $serviceName = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @throws \ReflectionException
     *
     * @return mixed
     */
    public function handle()
    {
        global $argv;

        if (in_array($action = $this->argument('action'), ['status', 'start', 'stop', 'restart', 'reload', 'connections'])) {

            $this->serviceName = $this->argument('serviceName');
            $daemon = $this->option('d') ? '-d' : '';

            $class = $this->config("service");

            if (empty($class)) {
                $this->error("{$this->serviceName}'s GatewayWorker config doesn't exist");
            } else {
                $argv[0] = 'gateway-worker ' . $this->serviceName;
                $argv[1] = $action;
                $argv[2] = $daemon;

                $service = new $class();
                try {
                    $service->start();
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }

        } else {
            $this->error('Invalid Arguments');
        }
    }
}
