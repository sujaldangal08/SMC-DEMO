<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ExecuteTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:execute-task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $contact = Http::get('http://smc-laravel-api.test/api/v1/xero/contacts');
        $response = Http::get('http://smc-laravel-api.test/api/v1/xero/purchase-orders');

        return 'Task executed successfully.';
    }
}
