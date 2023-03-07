<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Wallet;

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
        $wallet_list = Wallet::where('wallet_type', 8)->with('currency')->get();
        if(!empty($wallet_list)) {
            foreach ($wallet_list as $key => $wallet) {
                $user = User::findOrFail($wallet->user_id);
                $balance = Crypto_Balance($wallet->user_id, $wallet->currency_id);
                if($balance > $wallet->balance ) {
                    send_telegram($wallet->user_id, "Your ".$wallet->currency->code." wallet 's balance is updated .\n ".($balance-$wallet->balance).$wallet->currency->code." is incoming in your wallet. \n Please check your wallet. \n Your wallet address is ".$wallet->wallet_no);
                    send_whatsapp($wallet->user_id, "Your ".$wallet->currency->code." wallet 's balance is updated .\n ".($balance-$wallet->balance).$wallet->currency->code." is incoming in your wallet. \n Please check your wallet. \n Your wallet address is ".$wallet->wallet_no);
                    $u_wallet = Wallet::findOrFail($wallet->id);
                    $u_wallet->balance = $balance;
                    $u_wallet->save();
                }
            }
        }
        return 0;
    }
}
