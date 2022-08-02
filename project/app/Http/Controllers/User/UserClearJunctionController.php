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

class UserClearJunctionController extends Controller
{
    private $url = 'https://sandbox.clearjunction.com'; // Sandbox Host Url
    private $client_uuid = '8f297d61-5391-4a1e-83e8-147223520050'; // Sandbox Host Url
    private $wallet_uuid = '8f297da3-8838-437f-b4e1-ce9f7714c61b'; // Sandbox Host Url
    private $API_Key = '8f299ac0-1543-41f6-b094-70a5fd837bed';
    private $apiPassword = 'eydy8qv9ui0o';
    //private $apiPassword = hash("sha512", 'eydy8qv9ui0o');

    
	//echo $hashed = hash("sha512", $password);

    public function bankPayout(Request $request) {
        $client = new  Client();
        $response = $client->request('post', 'https://private-anon-69ce182ee3-clearjunctionrestapi.apiary-mock.com/v7/gate/payout/bankTransfer/swift?checkOnly=false', [
            'headers' => [
               'Accept'=> 'application/json',
               'Date'=> date("Y-m-d", time()) . 'T' . date("H:i:s", time()) .'+00:00',
              'X-API-KEY' => $this->API_Key,
              'Authorization' => hash("sha512", $apiPassword),
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
                "ledger_from_id": "'.$request->ledgerid.'",
                "beneficiary_id": "'.$request->beneficiaryid.'",
                "payment_type": "'.$request->paymenttype.'",
                "amount": "'.$request->amount.'"
              }',
            'headers' => [
               'Accept'=> 'application/json',
              'Authorization' => 'API-Key '.$this->API_Key,
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }


//////////////////////////////////////////////////// Other API ///////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////// Beneficiary /////////////////////////////////////////////////////////////////
    public function UpdateBeneficiary(Request $request, $id) {
        $client = new  Client();
        $response = $client->request('PUT', $this->url.'/beneficiaries/'.$id, [
            'body' => '{
                "iban": "'.$request->ibanid.'",
                "bank_country": "'.$request->bankcountry.'"
              }',
            'headers' => [
               'Accept'=> 'application/json',
              'Authorization' => 'API-Key '.$this->API_Key,
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateBeneficiaryAccount(Request $request, $id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/beneficiaries/'.$id.'/accounts', [
            'body' => '{
                "asset_type": "'.$request->asset_type.'",
                "asset_class": "currency"
              }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetBeneficiaryAccountList($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/beneficiaries/'.$id.'/accounts', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetBeneficiaryAccount($id, $accountid) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/beneficiaries/'.$id.'/accounts/'.$accountid, [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function UpdateBeneficiaryAccount(Request $request, $id, $accountid) {
        $client = new  Client();
        $response = $client->request('PUT', $this->url.'/beneficiaries/'.$id.'/accounts/'.$accountid, [
            'body' => '{
                "bank_code_type": "'.$request->bankcode.'",
                "bank_country": "'.$request->bankcountry.'",
              }',
            'headers' => [
               'Accept'=> 'application/json',
              'Authorization' => 'API-Key '.$this->API_Key,
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function ChangeBeneficiaryDefaultAccount($id, $accountid) {
        $client = new  Client();
        $response = $client->request('PUT', $this->url.'/beneficiaries/'.$id.'/accounts/'.$accountid.'/make-default', [
            'headers' => [
               'Accept'=> 'application/json',
              'Authorization' => 'API-Key '.$this->API_Key,
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetBeneficiaryCal($id) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/beneficiaries/'.$id.'/compliance-firewall-calculation', [
            'headers' => [
               'Accept'=> 'application/json',
              'Authorization' => 'API-Key '.$this->API_Key,
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetBeneficiaryStatus($id) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/beneficiaries/'.$id.'/wait', [
            'headers' => [
               'Accept'=> 'application/json',
              'Authorization' => 'API-Key '.$this->API_Key,
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

//////////////////////////////////////////////////// Card ////////////////////////////////////////////////////////////////////////
    public function GetCardRuleCounter($id) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/card-rule-counters/'.$id, [
            'headers' => [
            'Accept'=> 'application/json',
            'Authorization' => 'API-Key '.$this->API_Key,
            'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateCard(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/cards', [
            'body' => '{
                "additional_ledgers":  [{
                    "ledger_id" : "'.$request->legder_id.'"
                }],
                "card_programme":"'.$request->card_program.'",
                "asset_class": "'.$request->assetclass.'"
              }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetCardList() {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/cards', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetCardTokenDetails($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/cards/by-token/'.$id, [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateCardRule(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/cards/rules', [
            'body' => '{
                "card_rule_type": "'.$request->type.'",
                "card_rule_name": "'.$request->cardrulename.'",
                "card_rule_body": "'.$request->rulebody.'",
                "card_rule_description": "Rule declining all payments with amount above 1000"
              }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetCardRuleList() {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/cards/rules', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetCardRule($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/cards/rules/'.$id, [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function UpdateCardRule(Request $request, $id) {
        $client = new Client();
        $response = $client->request('PUT', $this->url.'/cards/rules/'.$id, [
            'body' => '{
                "card_rule_name": "'.$request->cardrulename.'"
              }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function DisableCardRule($id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/cards/rules/'.$id.'/disable', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetCard($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/cards/'.$id, [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function UpdateCard(Request $request, $id) {
        $client = new Client();
        $response = $client->request('PUT', $this->url.'/cards/'.$id, [
            'body' => '{
                "card_rules": [
                    "'.$request->card_rule_0.'",
                    "'.$request->card_rule_1.'"
                    ]
              }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function ActivateCard($id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/cards/'.$id.'/activate', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function AddLedgersToCard(Request $request, $id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/cards/'.$id.'/additional-ledgers', [
            'body' => '{
                "ledger_id": "'.$request->ledger_id.'"
              }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function UpdateCardholder(Request $request, $id) {
        $client = new Client();
        $response = $client->request('PUT', $this->url.'/cards/'.$id.'/cardholder-details', [
            'body' => '{
                "email": "'.$request->email.'",
                "telephone": "'.$request->telephone.'"
              }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CloseCard($id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/cards/'.$id.'/close', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetCardImage($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/cards/'.$id.'/image', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetCardPin($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/cards/'.$id.'/pin', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function ResetCardPin($id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/cards/'.$id.'/pin/reset', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function ReissueCard(Request $request ,$id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/cards/'.$id.'/reissue', [
            'body' => '{
                "name_on_card": "string",
                "card_carrier_type": "renewal",
                "card_delivery_name": "n",
                "qr_code_content": "Any reasonably long string, e.g. description of something",
                "telephone": "0012345678912",
                "card_rules": [
                  "c91b339e-57d7-41ea-a805-8966ce8fe4ed",
                  "753fa673-66b4-4c94-9ddb-f9f4b5c1e9a3",
                  "753fa673-66b4-4c94-9ddb-f9f4b5c1e9a3"
                ],
                "card_delivery_method": "international-mail",
                "card_design": "some-chars",
                "card_delivery_address": {
                  "address_number": "47",
                  "address_refinement": "Apartment 1",
                  "address_region": "California",
                  "address_iso_country": "GB",
                  "address_street": "Riverside Drive",
                  "address_city": "Los Angeles",
                  "address_postal_code": "123456"
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

    public function ReplaceCard(Request $request ,$id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/cards/'.$id.'/replace', [
            'body' => '{
                "name_on_card": "string",
                "card_carrier_type": "renewal",
                "card_delivery_name": "n",
                "qr_code_content": "Any reasonably long string, e.g. description of something",
                "telephone": "0012345678912",
                "card_rules": [
                  "c91b339e-57d7-41ea-a805-8966ce8fe4ed",
                  "753fa673-66b4-4c94-9ddb-f9f4b5c1e9a3",
                  "753fa673-66b4-4c94-9ddb-f9f4b5c1e9a3"
                ],
                "card_delivery_method": "international-mail",
                "card_design": "some-chars",
                "card_delivery_address": {
                  "address_number": "47",
                  "address_refinement": "Apartment 1",
                  "address_region": "California",
                  "address_iso_country": "GB",
                  "address_street": "Riverside Drive",
                  "address_city": "Los Angeles",
                  "address_postal_code": "123456"
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

    public function SuspendCard($id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/cards/'.$id.'/suspend', [
            'body' => '{
                "suspend_reason": "card-lost"
              }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetPaymentTokenDetail($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/payment-tokens/by-token/'.$id, [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetPaymentTokenIdDetail($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/payment-tokens/'.$id, [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function ActivatePaymentToken($id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/payment-tokens/'.$id.'/activate', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetPaymentTokenActivationCode($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/payment-tokens/'.$id.'/activation-code', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function ClosePaymentToken($id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/payment-tokens/'.$id.'/close', [
            'body' => '{
                "close_reason": "payment-token-stolen"
              }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function SuspendPaymentToken(Request $request,$id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/payment-tokens/'.$id.'/suspend', [
            'body' => '{
                "suspend_reason": "'.$request->reason.'"
              }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

//////////////////////////////////////////////////// Compliance //////////////////////////////////////////////////////////////////

    public function CreateComplianceContact(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/compliance-contact', [
            'body' => '{
                "email": "'.$request->email.'",
                "slack_domain": "'.$request->slackdn.'",
                "slack_channel": "'.$request->slackch.'"
            }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetComplianceContact() {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/compliance-contact', [
            'body' => '{
                "email": "'.$request->email.'",
                "slack_domain": "'.$request->slackdn.'",
                "slack_channel": "'.$request->slackch.'"
            }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function FirewallRules(Request $request, $id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/compliance-firewall-rules/'.$id, [
            'body' => '{
                "compliance_firewall_rules": "'.$request->firewall_rules.'"
            }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetFirewallRules($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/compliance-firewall-rules/'.$id, [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetFirewallRulesHistory($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/compliance-firewall-rules/'.$id.'/history', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function ReloadRules($id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/compliance-firewall-rules/'.$id.'/reload', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function ReloadRules(Request $request, $id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/compliance-firewall-rules/'.$id.'/test', [
            'body' => '{
                "compliance_firewall_rules": "'.$request->firewall_rules.'",
                "transaction_id": "'.$request->transaction_id.'"
            }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateFirewallDataset(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/compliance-firewall-static-data', [
            'body' => '{
                "dataset_data": "'.$request->dataset_data.'",
                "dataset_name": "'.$request->dataset_name.'"
            }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetFirewallDataset($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/compliance-firewall-static-data/'.$id, [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetAllFirewallDataset() {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/compliance-manual', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetAllQuarantine() {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/quarantine', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetQuarantinedBeneficiaryList() {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/quarantine/beneficiaries', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetQuarantinedBeneficiary($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/quarantine/beneficiaries/'.$id, [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function AddCommentBeneficiary(Request $request,$id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/quarantine/beneficiaries/'.$id.'/comments', [
            'body' => '{
                "comment": "'.$request->comment.'"
                }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function ResolveQuarantinedBeneficiary(Request $request,$id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/quarantine/beneficiaries/'.$id.'/resolve', [
            'body' => '{
                "comment": "'.$request->comment.'",
                "qr_status": "'.$request->qrstatus.'"
                }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetQuarantinedEnduserList($counter, $position) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/quarantine/endusers?items_per_page='.$counter.'&offset='.$position, [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetQuarantinedEnduser($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/quarantine/endusers/'.$id, [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function AddCommentEnduser(Request $request,$id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/quarantine/endusers/'.$id.'/comments', [
            'body' => '{
                "comment": "'.$request->comment.'"
                }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function ResolveQuarantinedEnduser(Request $request,$id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/quarantine/endusers/'.$id.'/resolve', [
            'body' => '{
                "comment": "'.$request->comment.'",
                "qr_status": "'.$request->qrstatus.'"
                }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetQuarantinedTransactionList($counter, $position) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/quarantine/transactions?items_per_page='.$counter.'&offset='.$position, [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetQuarantinedTransaction($id) {
        $client = new Client();
        $response = $client->request('GET', $this->url.'/quarantine/transactions/'.$id, [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function AddCommentTransaction(Request $request,$id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/quarantine/transactions/'.$id.'/comments', [
            'body' => '{
                "comment": "'.$request->comment.'"
                }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function ResolveQuarantinedTransaction(Request $request,$id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/quarantine/transactions/'.$id.'/resolve', [
            'body' => '{
                "comment": "'.$request->comment.'",
                "qr_status": "'.$request->qrstatus.'"
                }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

//////////////////////////////////////////////////// EndUsers ////////////////////////////////////////////////////////////////////

    public function GetEnduser($id) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/endusers/'.$id, [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function UpdateEnduser(Request $request, $id) {
        $client = new  Client();
        $response = $client->request('PUT', $this->url.'/endusers/'.$id, [
            'body' => '{
                "email": "'.$request->email.'",
                "name": "'.$request->name.'"
                }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateEnduserAgreement(Request $request, $id) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/endusers/'.$id.'/agreements', [
            'body' => '{
                "agreement_type": "'.$request->type.'",
                "agreement_acceptance_date": "'.$request->acceptdate.'",
                "partner_product": "'.$request->product.'",
                "agreement_consent": '.$request->consent.',
              }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetEnduserAgreementList($id) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/endusers/'.$id.'/agreements', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetEnduserCal($id) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/endusers/'.$id.'/compliance-firewall-calculation', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateEnduserAgreement(Request $request, $id) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/endusers/'.$id.'/credit-checks', [
            'body' => '{
                "partner_product": "'.$request->product.'"
              }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetEnduserCreditDetails($id, $credit_id) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/endusers/'.$id.'/credit-checks/'.$credit_id, [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateCreditOfferAcceptance($id, $credit_id) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/endusers/'.$id.'/credit-checks/'.$credit_id.'/accept', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateEnduserKYCCheck(Request $request, $id) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/endusers/'.$id.'/kyc-checks', [
            'body' => '{
                "trusted_bureau_result": {
                  "trusted_bureau_meta": {
                    '.$request->tbm.'
                  },
                  "created_at": "'.$request->trust_create.'",
                  "trusted_bureau_ref": "'.$request->tbr.'",
                  "trusted_bureau_status": "'.$request->tbs.'",
                  "trusted_bureau_source": "'.$request->tbsource.'",
                },
                "application_id": "com.customer-company.app-name",
                "referrer": "abcdefghijklmnopqrstuvwxyz",
                "screening_result": {
                  "created_at": "'.$request->screen_create.'",
                  "screening_source": "'.$request->screensource.'",
                  "screening_status": "'.$request->screenstatus.'",
                  "screening_ref": "'.$request->screenref.'",
                  "screening_meta": {
                    '.$request->screenmeta.'
                  }
                },
                "idv_result": {
                  "created_at": "'.$request->idv_create.'",
                  "idv_status": "'.$request->idv_status.'",
                  "idv_source": "'.$request->idv_source.'",
                  "idv_meta": {
                    '.$request->idv_meta.'
                  },
                  "idv_ref": "'.$request->idv_ref.'"
                },
                "kyc_consent": '.$request->kyc_consent.',
                "fraud_check_result": {
                  "created_at": "'.$request->fraud_create.'",
                  "fraud_check_source": "'.$request->fraud_source.'",
                  "fraud_check_status": "'.$request->fraud_status.'",
                  "fraud_check_meta": {
                    '.$request->fraud_meta.'
                  },
                  "fraud_check_ref": "'.$request->fraud_ref.'"
                },
                "partner_product": "'.$request->product.'"
              }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetEnduserKYCCheckList($id) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/endusers/'.$id.'/kyc-checks', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetEnduserKYCCheck($id, $kycid) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/endusers/'.$id.'/kyc-checks/'.$kycid, [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function AddEnduserKYCFiles(Request $request ,$id, $kycid) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/endusers/'.$id.'/kyc-checks/'.$kycid.'/files', [
            'body' => '{
                "file": "'.$request->file.'"
                "type": "'.$request->type.'"
              }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetEnduserKYCFilesList($id, $kycid) {
        $client = new  Client();
        $response = $client->request('Get', $this->url.'/endusers/'.$id.'/kyc-checks/'.$kycid.'/files', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateKYCCheck(Request $request ,$id) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/endusers/'.$id.'/kyc-initiate-check', [
            'body' => '{
                "kyc_id": "'.$request->kyc_id.'"
              }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function ReapplyEnduserfirewall($id) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/endusers/'.$id.'/rerun-firewall', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateVirtualLedger(Request $request) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/ledgers/virtual', [
            'body' => '{
                "asset_class": "'.$request->asset_class.'",
                "ledger_meta": {
                  "foo": "bar"
                },
                "asset_type": "'.$request->asset_type.'",
                "holder_id": "'.$request->holder_id.'",
                "product": "Railsbank-Rewards-1"
              }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function Updateledger(Request $request, $id) {
        $client = new Client();
        $response = $client->request('PUT', $this->url.'/ledgers/'.$id, [
            'body' => '{
                "ledger_meta": {
                    '.$request->meta.'
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

    public function CloseLedger($id) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/ledgers/'.$id.'/close', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetLedgerHistory($id) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/ledgers/'.$id.'/entries', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

//////////////////////////////////////////////////// Customer Configuration //////////////////////////////////////////////////////

    public function GetMeInformation() {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/me', [
            'headers' => [
            'Accept'=> 'application/json',
            'Authorization' => 'API-Key '.$this->API_Key,
            'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetMyBankList() {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/my/partners', [
            'headers' => [
            'Accept'=> 'application/json',
            'Authorization' => 'API-Key '.$this->API_Key,
            'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetEnabledProductList() {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/my/products', [
            'headers' => [
            'Accept'=> 'application/json',
            'Authorization' => 'API-Key '.$this->API_Key,
            'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetAssignedCardList($id) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/my/products/'.$id.'/card-programmes', [
            'headers' => [
            'Accept'=> 'application/json',
            'Authorization' => 'API-Key '.$this->API_Key,
            'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

//////////////////////////////////////////////////// WebHooks ////////////////////////////////////////////////////////////////////

    public function GetWebhookHistory($counter, $position) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/notifications?items_per_page='.$counter.'&offset='.$position, [
            'headers' => [
            'Accept'=> 'application/json',
            'Authorization' => 'API-Key '.$this->API_Key,
            'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateWebhook(Request $request) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/webhook', [
            'body' => '{
                "webhook_secret": "'.$request->secret.'",
                "webhook_url": "'.$request->url.'"
              }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function DeleteWebhook() {
        $client = new  Client();
        $response = $client->request('DELETE', $this->url.'/webhook', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetWebhook() {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/webhook', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetFailedWebhook() {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/webhook/failed-to-deliver', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function UpdateWebhook(Request $request) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/webhook/update-secret', [
            'body' => '{
                "webhook_secret": "'.$request->secret.'"
              }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

//////////////////////////////////////////////////// Transactions ////////////////////////////////////////////////////////////////
    public function ConvertSendTransaction(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/transactions/fx', [
            'body' => '{
                "payment_method": "'.$request->method.'",
                "enduser_verified_transaction": "recurring-transactions",
                "fixed_side": "'.$request->fixedside.'",
                "reference": "'.$request->reference.'",
                "amount": "'.$request->amount.'",
                "ledger_from_id": "'.$request->fromid.'",
                "reason": "'.$request->reason.'",
                "beneficiary_id": "'.$request->benefit_id.'",
                "transaction_meta": {
                    "foo": "bar"
                },
                "beneficiary_account_id": "8763b5df-a61c-4f77-9724-41ca9cde3654"
            }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetExchangeRates(Request $request) {
        $client = new Client();
        $response = $client->request('PUT', $this->url.'/transactions/fx/quote', [
            'body' => '{
                "sender_asset_type": "'.$request->sendertype.'",
                "fixed_side": "'.$request->fixedside.'",
                "amount": "'.$request->amount.'",
                "beneficiary_asset_type": "'.$request->beneficiarytype.'"
            }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateInterTransaction(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/transactions/inter-ledger', [
            'body' => '{
                "ledger_to_id": "'.$request->toid.'",
                "ledger_from_id": "'.$request->fromid.'",
                "amount": "'.$request->amount.'",
                "transaction_meta": {
                  "foo": "bar"
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

    public function TestInterledger(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/transactions/inter-ledger/try', [
            'body' => '{
                "ledger_to_id": "'.$request->toid.'",
                "ledger_from_id": "'.$request->fromid.'",
                "amount": "'.$request->amount.'",
                "transaction_meta": {
                  "foo": "bar"
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

    public function CreditVirtualLedger(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/transactions/manual-credit', [
            'body' => '{
                "transaction_meta": {
                    "foo": "bar"
                  },
                  "amount": "'.$request->amount.'",
                  "ledger_id": "'.$request->ledger_id.'"
            }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function DebitVirtualLedger(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/transactions/manual-debit', [
            'body' => '{
                "transaction_meta": {
                    "foo": "bar"
                  },
                  "amount": "'.$request->amount.'",
                  "ledger_id": "'.$request->ledger_id.'"
            }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function TestTransaction(Request $request) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/transactions/try', [
            'body' => '{
                  "amount": "'.$request->amount.'",
                  "payment_type": "'.$request->type.'"
            }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function UpdateTransactionMetadata(Request $request, $id) {
        $client = new Client();
        $response = $client->request('PUT', $this->url.'/transactions/'.$id, [
            'body' => '{
                "transaction_meta": {
                    '.$request->meta.'
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

    public function GetTransactionCal($id) {
        $client = new Client();
        $response = $client->request('PUT', $this->url.'/transactions/'.$id.'/compliance-firewall-calculation', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function RerunTransaction($id) {
        $client = new Client();
        $response = $client->request('POST', $this->url.'/transactions/'.$id.'/rerun-firewall', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

//////////////////////////////////////////////////// Credit //////////////////////////////////////////////////////////////////////

    public function CreateLinkExternalBank(Request $request,$id) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/ledgers/'.$id.'/external-accounts', [
            'body' => '{
                "number": "'.$request->number.'",
                "bank_code": "'.$request->bankcode.'",
                "name": "'.$request->name.'",
                "account_type": "'.$request->account_type.'",
                "bank_name": "'.$request->bankname.'",
                "type": "'.$request->type.'",
                "legal_type": "person",
                "external_account_meta": {}
              }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetLinkExternalBankList($id) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/ledgers/'.$id.'/external-accounts', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateScheduleCredit(Request $request,$id) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/ledgers/'.$id.'/scheduled-credit-payments', [
            'body' => '{
                "external_account_id": "'.$request->external_id.'",
                "payment_amount_type": "'.$request->amount_type.'",
                "amount": '.$request->amount.',
                "payment_date": "'.$request->payday.'",
                "payment_type": "'.$request->payment_type.'",
                "agreement_id": "'.$request->agreement_id.'"
              }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetScheduleCreditList($id) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/ledgers/'.$id.'/scheduled-credit-payments', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateScheduleCreditAutoPay(Request $request,$id) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/ledgers/'.$id.'/scheduled-credit-payments/autopay', [
            'body' => '{
                "external_account_id": "'.$request->external_id.'",
                "payment_amount_type": "'.$request->amount_type.'",
                "days_before_due_date": "'.$request->pday.'",
                "payment_type": "'.$request->payment_type.'",
                "agreement_id": "'.$request->agreement_id.'"
              }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CancelCreditPayment($id,$schedule_id) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/ledgers/'.$id.'/scheduled-credit-payments/'.$schedule_id.'/cancel', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function ApplyCreditLedger(Request $request,$id) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/ledgers/'.$id.'/credit-adjustments', [
            'body' => '{
                "amount": '.$request->amount.',
                "reference": "'.$request->reference.'",
                "credit_adjustment_type": "'.$request->type.'"
              }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetCreditAdjustmentList($id) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/ledgers/'.$id.'/credit-adjustments', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function FetchLinkedExternalAccount($id,$account_id) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/ledgers/'.$id.'/external-accounts/'.$account_id, [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function DeleteLinkedExternalAccount($id,$account_id) {
        $client = new  Client();
        $response = $client->request('DELETE', $this->url.'/ledgers/'.$id.'/external-accounts/'.$account_id, [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function FetchCreditStatement($id,$detail_id) {
        $client = new  Client();
        $response = $client->request('GET', $this->url.'/ledgers/'.$id.'/credit-details/'.$detail_id.'/credit-statements', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function ReverseCreditAdjustment($id,$adjustment_id) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/ledgers/'.$id.'/credit-adjustments/'.$adjustment_id.'/reversal', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function ApplyFeeDebit(Request $request,$id) {
        $client = new  Client();
        $response = $client->request('POST', $this->url.'/ledgers/'.$id.'/debit-adjustments', [
            'body' => '{
                "amount": '.$request->amount.',
                "reference": "'.$request->reference.'",
                "debit_adjustment_type": "'.$request->type.'"
              }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$this->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

}
