<?php

namespace App\Http\Controllers\Deposit;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Generalsetting;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class RailsbankController extends Controller
{
    public $public_key;
    private $secret_key;

    public function __construct()
    {
        $data = PaymentGateway::whereKeyword('railsbank')->first();
        $paydata = $data->convertAutoData();
        $this->api_key = $paydata['api_key'];
        $this->api_token = $paydata['api_token'];
    }

    public function store(Request $request) {
        $curl = curl_init();

        

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://play.railsbank.com",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
              // 'amount' => $item_amount,
              // 'customer_email' => $customer_email,
              // 'currency' => $currency,
              // 'txref' => $txref,
              // 'PBFPubKey' => $PBFPubKey,
              // 'redirect_url' => $redirect_url,
              // 'payment_plan' => $payment_plan
            ]),
            CURLOPT_HTTPHEADER => [
              "content-type: application/json",
              "cache-control: no-cache"
            ],
          ));
          
          $response = curl_exec($curl);
          $err = curl_error($curl);
          
          if($err){
            die('Curl returned error: ' . $err);
          }
          
          $transaction = json_decode($response);
          
          if(!$transaction->data && !$transaction->data->link){
            print_r('API returned error: ' . $transaction->message);
          }
          
          return redirect($transaction->data->link);

     }

     
}
