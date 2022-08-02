<?php

namespace App\Http\Controllers\API;

use App\Models\BankPlan;
use App\Models\Currency;
use App\Models\OtherBank;
use App\Models\Beneficiary;
use App\Models\UserApiCred;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Models\BalanceTransfer;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;

class UserClearJunctionController extends Controller
{
    private $url = 'https://private-anon-69ce182ee3-clearjunctionrestapi.apiary-mock.com'; // Sandbox Host Url
    private $client_uuid = '8f297d61-5391-4a1e-83e8-147223520050'; // Sandbox Host Url
    private $wallet_uuid = '8f297da3-8838-437f-b4e1-ce9f7714c61b'; // Sandbox Host Url
    private $API_Key = '8f299ac0-1543-41f6-b094-70a5fd837bed';
    private $apiPassword = 'eydy8qv9ui0o';
    private $postbackUrl = 'http://iteverest.saas.test:8080/bankwallet/api/user';
    //private $apiPassword = hash("sha512", 'eydy8qv9ui0o');

    
	//echo $hashed = hash("sha512", $password);

    public function bankPayout(Request $request) {
        try{
        $user_id = UserApiCred::where('access_key', $request->access_key)->first()->user_id;

        $user = User::whereId($user_id)->first();
        

            $client = new  Client();
            $response = $client->request('post', $this->url.'/v7/gate/payout/bankTransfer/swift?checkOnly=true', [
                'body' => '{
                    "clientOrder": "'.$request->transaction_id.'",
                    "currency": "'.$request->currency.'",
                    "amount": '.$request->amound.',
                    "description": "'.$request->description.'",
                    "postbackUrl": "'.$this->postbackUrl.'",
                    "customInfo": {
                    },
                    "payer": {
                      "clientCustomerId": "'.$this->client_uuid.'",
                      "walletUuid": "'.$this->wallet_uuid.'",
                      "individual": {
                        "phone": "34712345678",
                        "email": "peterson.julie@example.com",
                        "birthDate": "1999-09-29",
                        "birthPlace": "Madrid, Spain",
                        "address": {
                          "country": "IT",
                          "zip": "123455",
                          "city": "Rome",
                          "street": "12 Tourin"
                        },
                        "document": {
                          "type": "passport",
                          "number": "AB1000222",
                          "issuedCountryCode": "IT",
                          "issuedBy": "Ministry of Interior",
                          "issuedDate": "2016-12-21",
                          "expirationDate": "2026-12-20"
                        },
                        "lastName": "Peterson",
                        "firstName": "Julie",
                        "middleName": "Maria"
                      }
                    },
                    "payee": {
                    "clientCustomerId": "'.$this->client_uuid.'",
                    "walletUuid": "'.$this->wallet_uuid.'",
                      "individual": {
                        "phone": "34712345678",
                        "email": "peterson.julie@example.com",
                        "birthDate": "1999-09-29",
                        "birthPlace": "Madrid, Spain",
                        "address": {
                          "country": "IT",
                          "zip": "123455",
                          "city": "Rome",
                          "street": "12 Tourin"
                        },
                        "document": {
                          "type": "passport",
                          "number": "AB1000222",
                          "issuedCountryCode": "IT",
                          "issuedBy": "Ministry of Interior",
                          "issuedDate": "2016-12-21",
                          "expirationDate": "2026-12-20"
                        },
                        "lastName": "Peterson",
                        "firstName": "Julie",
                        "middleName": "Maria"
                      }
                    },
                    "payeeRequisite": {
                      "bankAccountNumber": "ES9121000418450200051332",
                      "bankName": "Bank of America",
                      "bankSwiftCode": "UBSWCHZH80A",
                      "intermediaryInstitution": {
                        "bankCode": "026009593",
                        "bankName": "Bank of America"
                      }
                    },
                    "payerRequisite": {
                      "bankAccountNumber": "ES9121000418450200051332",
                      "bankName": "Bank of America",
                      "bankSwiftCode": "UBSWCHZH80A",
                      "intermediaryInstitution": {
                        "bankCode": "026009593",
                        "bankName": "Bank of America"
                      }
                    }
                  }',
                'headers' => [
                'Accept'=> 'application/json',
                'Date'=> date("Y-m-d", time()) . 'T' . date("H:i:s", time()) .'+00:00',
                'X-API-KEY' => $this->API_Key,
                'Authorization' => hash("sha512", $this->apiPassword),
                'Content-Type' => 'application/json',
                
                ],
            ]);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
        }catch(\Throwable $th){
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
        }
    }

    
}
