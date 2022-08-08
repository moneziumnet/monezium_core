<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Models\BalanceTransfer;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;

class UserEMbankController extends Controller
{
    private $url = 'https://api.cabel.it/emb-aisp-sandbox/digx-sandbox/lz/obuk/v3.1/aisp';
    private $API_Key = '';

    public function Accounts(Request $request) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/accounts', [
            'body' => json_encode($request->all()),
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    // Create consent
    public function CreateConsent(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/account-access-consents', [
            'body' => json_encode($request->all()),
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }
}
