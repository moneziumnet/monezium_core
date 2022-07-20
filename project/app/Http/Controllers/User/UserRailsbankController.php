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

class UserRailsbankController extends Controller
{
    private $url = 'https://play.railsbank.com/v1/customer';
    private $API_Key = 'xt2siykg3y47yf6c1k7j96z2r6g0enox#jmg0hrwibt7nlrx1cf2m2wl1lkhinhw4kvge2tgl5jtjkhbrxfrrwm7o95leymlr';

    public function GetEnduserList() {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/endusers', [
            'headers' => [
               'Accept'=> 'application/json',
              'Authorization' => 'API-Key '.$this->API_Key,
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateEnduser(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/endusers', [
            'body' => '{
                "person": {
                  "name": "'.$request->name.'",
                  "email": "'.$request->email.'",
                  "address": { "address_iso_country": "'.$request->countrycode.'" }
                }
            }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CheckEnduserStatus($id) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/endusers/'.$id.'/wait', [
            'headers' => [
               'Accept'=> 'application/json',
              'Authorization' => 'API-Key '.$this->API_Key,
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function Createledger(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/ledgers', [
            'body' => '{
                "holder_id": "'.$request->enduserid.'",
                "partner_product": "'.$request->partnerbank.'",
                "asset_class": "currency",
                "asset_type": "'.$request->currencycode.'",
                "ledger-type": "ledger-type-single-user",
                "ledger-who-owns-assets": "ledger-assets-owned-by-me",
                "ledger-primary-use-types": ["ledger-primary-use-types-payments"],
                "ledger-t-and-cs-country-of-jurisdiction": "'.$request->countrycode.'"
              }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetLegderList() {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/ledgers', [
            'headers' => [
               'Accept'=> 'application/json',
              'Authorization' => 'API-Key '.$this->API_Key,
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetLegder($id) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/ledgers/'.$id, [
            'headers' => [
               'Accept'=> 'application/json',
              'Authorization' => 'API-Key '.$this->API_Key,
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function Assigniban($id) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/ledgers/'.$id.'/assign-iban', [
            'headers' => [
               'Accept'=> 'application/json',
              'Authorization' => 'API-Key '.$this->API_Key,
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetTransaction($id) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/transactions/'.$id, [
            'headers' => [
               'Accept'=> 'application/json',
              'Authorization' => 'API-Key '.$this->API_Key,
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetTransactionList() {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/transactions', [
            'headers' => [
               'Accept'=> 'application/json',
              'Authorization' => 'API-Key '.$this->API_Key,
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateBeneficiary(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/beneficiaries', [
            'body' => '{
                holder_id: '.$request->enduserid.',
                asset_class: "currency",
                asset_type: "'.$request->currencycode.'",
                iban: "'.$request->senderiban.'",
                bic_swift: "'.$request->senderbic.'",
                person: {
                  name: "'.$request->sendername.'",
                  address: { address_iso_country: "'.$request->sendercountrycode.'" },
                  email: "'.$request->senderemail.'"
                }
              }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetBeneficiaryList() {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/beneficiaries', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetBeneficiary($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/beneficiaries/'.$id, [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateTransfer(Request $request) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/transactions', [
            'body' => '{
                ledger_from_id: '.$request->ledgerid.',
                beneficiary_id: '.$request->beneficiaryid.',
                payment_type: "'.$request->paymenttype.'",
                amount: "'.$request->amount.'"
              }',
            'headers' => [
               'Accept'=> 'application/json',
              'Authorization' => 'API-Key '.$this->API_Key,
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }
}
