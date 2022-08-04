<?php

namespace App\Http\Controllers\User;

use App\Models\BankPlan;
use App\Models\Currency;
use App\Models\OtherBank;
use App\Models\Beneficiary;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Models\BalanceTransfer;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use DateTime;

class UserClearJunctionController extends Controller
{
    private $url = 'https://sandbox.clearjunction.com/v7/'; // Sandbox Host Url
    private $client_uuid = '8f297d61-5391-4a1e-83e8-147223520050'; // Sandbox Host Url
    private $wallet_uuid = '8f297da3-8838-437f-b4e1-ce9f7714c61b'; // Sandbox Host Url
    private $API_Key = '8f299ac0-1543-41f6-b094-70a5fd837bed';
    private $apiPassword = 'eydy8qv9ui0o';
    //private $apiPassword = hash("sha512", 'eydy8qv9ui0o');


	//echo $hashed = hash("sha512", $password);
    public function gettoken($request) {
        $secret = hash('sha512', $this->apiPassword);
        $datetime = new DateTime();
        $now = $datetime->format(DateTime::ATOM);
        // $body = json_encode($request);
        $signature = hash('sha512', mb_strtoupper($this->API_Key).$now.mb_strtoupper($secret).mb_strtoupper($request));
        return array($signature, $now);
    }

    public function bankwallets(Request $request) {
        $client = new  Client();
        $param = $this->gettoken(json_encode($request->all()));
        $response = $client->request('GET',  $this->url.'bank/wallets/'.$this->wallet_uuid.'?returnPaymentMethods=true', [
            'body' => json_encode($request->all()),
            'headers' => [
               'Accept'=> '*/*',
              'X-API-KEY' => $this->API_Key,
              'Authorization' => 'Bearer '.$param[0],
              'Date' => $param[1],
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function walletstatement(Request $request) {
        $client = new  Client();
        $param = $this->gettoken(json_encode($request->all()));
        $response = $client->request('POST',  $this->url.'gate/wallets/statement', [
            'body' => json_encode($request->all()),
            'headers' => [
               'Accept'=> '*/*',
              'X-API-KEY' => $this->API_Key,
              'Authorization' => 'Bearer '.$param[0],
              'Date' => $param[1],
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function ibanindividual(Request $request) {
        $client = new  Client();
        $param = $this->gettoken(json_encode($request->all()));
        $response = $client->request('POST',  $this->url.'gate/iban/individual', [
            'body' => json_encode($request->all()),
            'headers' => [
               'Accept'=> '*/*',
              'X-API-KEY' => $this->API_Key,
              'Authorization' => 'Bearer '.$param[0],
              'Date' => $param[1],
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

}
