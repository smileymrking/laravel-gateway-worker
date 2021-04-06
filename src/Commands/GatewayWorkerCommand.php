<?php

namespace SmileyMrKing\GatewayWorker\Commands;

use Illuminate\Console\Command;

class GatewayWorkerCommand extends Command
{
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

            $serviceName = $this->argument('serviceName');
            $daemon = $this->option('d') ? '-d' : '';

            $class = config("gateway-worker.{$serviceName}.service");

            if (empty($class)) {
                $this->error("{$serviceName}'s GatewayWorker config doesn't exist");
            } else {
                $argv[0] = 'gateway-worker ' . $serviceName;
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
