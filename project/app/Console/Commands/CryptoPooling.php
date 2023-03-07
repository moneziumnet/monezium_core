<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Wallet;
use GuzzleHttp\Client;

class CryptoPooling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CryptoBalance:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is crypto balance check cron task.';

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
     *
     * @return int
     */
    public function handle()
    {
        info("Cron Job running at ". now());

        /*------------------------------------------
        --------------------------------------------
        Write Your Logic Here....
        I am getting users and create new users if not exist....
        --------------------------------------------
        --------------------------------------------*/
        $client = new Client();
        $response = $client->request('GET', 'http://monezium.eu/user/crypto/deposit/sms', ['connect_timeout' => 2]);
        return 0;
    }
}
