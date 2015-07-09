<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AppInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:init';

    private $queue_conf;

    private $socket_conf;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize application configuration';

    /**
     * Create a new console command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->queue_conf = env('APP_NAME', 'laravel') . '-queue';
        $this->socket_conf = env('APP_NAME', 'laravel') . '-socket';
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $app_path = base_path();
        $logs_path = storage_path('logs');
        $user = get_current_user();
        $app_name = env('APP_NAME', 'laravel');
        $laravel_queue = <<<FILE
[program:$app_name-queue]
process_name=%(program_name)s_%(process_num)02d
directory=$app_path
command=php artisan queue:work --sleep=3 --tries=3 --daemon
autostart=true
autorestart=true
user=$user
numprocs=8
redirect_stderr=true
stdout_logfile=$logs_path/$this->queue_conf.log
FILE;
        file_put_contents('/etc/supervisor/conf.d/' . $this->queue_conf . '.conf', $laravel_queue);

        $laravel_socket = <<<FILE
[program:$app_name-socket]
process_name=%(program_name)s_%(process_num)02d
directory=$app_path
command=node socket.js
autostart=true
autorestart=true
user=$user
numprocs=1
redirect_stderr=true
stdout_logfile=$logs_path/$this->socket_conf.log
FILE;
        file_put_contents('/etc/supervisor/conf.d/' . $this->socket_conf . '.conf', $laravel_socket);
        $this->info('Application initialized successfuly!');
    }

}
