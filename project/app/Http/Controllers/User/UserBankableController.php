<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Models\BalanceTransfer;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;

class UserBankableController extends Controller
{
    private $url = 'https://sandbox.treezor.com/v1/index.php/';
    private $accessSignature = '';
    private $accessTag = '';
    private $accessUserId = '';
    private $accessUserIp = '';
    private $walletId = '';
    private $API_Key = '';

    public function Balances(Request $request) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'balances', [
            'body' => json_encode($request->all()),
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'accessSignature' => $this->accessSignature,
                'accessTag' => $this->accessTag,
                'accessUserId' => $this->accessUserId,
                'accessUserIp' => $this->accessUserIp,
                'walletId' => $this->walletId,
                'userId' => $this->userId,
                'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

     // Create Bank Account
     public function CreateBankAccounts(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/bankaccounts', [
            'body' => json_encode($request->all()),
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'accessSignature' => $this->accessSignature,
                'accessTag' => $this->accessTag,
                'accessUserId' => $this->accessUserId,
                'accessUserIp' => $this->accessUserIp,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }
    
    public function BankAccountDetails(Request $request) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'bankaccounts/'.$this->request('account_id'), [
            'body' => json_encode($request->all()),
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'accessSignature' => $this->accessSignature,
                'accessTag' => $this->accessTag,
                'accessUserId' => $this->accessUserId,
                'accessUserIp' => $this->accessUserIp,
                'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

   

    public function DeleteBankAccount(Request $request) {
        $client = new  Client();
        $response = $client->request('DELETE', $this->url.'bankaccounts/'.$request->input('account_id'), [
            'body' => json_encode($request->all()),
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'accessSignature' => $this->accessSignature,
                'accessTag' => $this->accessTag,
                'accessUserId' => $this->accessUserId,
                'accessUserIp' => $this->accessUserIp,
                'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }
    
    public function Beneficiaries(Request $request) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'beneficiaries', [
            'body' => json_encode($request->all()),
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'accessSignature' => $this->accessSignature,
                'accessTag' => $this->accessTag,
                'accessUserId' => $this->accessUserId,
                'accessUserIp' => $this->accessUserIp,
                'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }
    
    public function SearchBeneficiaries(Request $request) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'beneficiaries', [
            'body' => json_encode($request->all()),
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'accessSignature' => $this->accessSignature,
                'accessTag' => $this->accessTag,
                'accessUserId' => $this->accessUserId,
                'accessUserIp' => $this->accessUserIp,
                'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }
    
    public function GetBeneficiaries(Request $request) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'beneficiaries/'.$request->input('id'), [
            'body' => json_encode($request->all()),
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'accessSignature' => $this->accessSignature,
                'accessTag' => $this->accessTag,
                'accessUserId' => $this->accessUserId,
                'accessUserIp' => $this->accessUserIp,
                'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }
    
    public function UpdateBeneficiaries(Request $request) {
        $client = new  Client();
        $response = $client->request('PUT', $this->url.'beneficiaries/'.$request->input('id'), [
            'body' => json_encode($request->all()),
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'accessSignature' => $this->accessSignature,
                'accessTag' => $this->accessTag,
                'accessUserId' => $this->accessUserId,
                'accessUserIp' => $this->accessUserIp,
                'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }
    
    public function Businesssearchs(Request $request) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'businesssearchs', [
            'body' => json_encode($request->all()),
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'accessSignature' => $this->accessSignature,
                'accessTag' => $this->accessTag,
                'accessUserId' => $this->accessUserId,
                'accessUserIp' => $this->accessUserIp,
                'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }
    
    public function Businessinformations(Request $request) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'businessinformations', [
            'body' => json_encode($request->all()),
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'accessSignature' => $this->accessSignature,
                'accessTag' => $this->accessTag,
                'accessUserId' => $this->accessUserId,
                'accessUserIp' => $this->accessUserIp,
                'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    
    
}
