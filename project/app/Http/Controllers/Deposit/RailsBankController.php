<?php

namespace App\Http\Controllers\Deposit;

use Illuminate\Support\Str;
use App\Classes\GeniusMailer;
use App\Models\BankGateway;
use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;

class RailsBankController extends Controller
{
    public function store(Request $request){
        $client = New Client();
        $user = User::findOrFail($request->user);
        $bankgateway = BankGateway::where('subbank_id', $request->subbank)->first();
        $bankaccount = BankAccount::where('user_id', $request->user)->where('subbank_id', $request->subbank)->where('currency_id', $request->currency)->first();
        if ($bankaccount){
            return redirect()->back()->with(array('warning' => 'This bank account already exists.'));

        }
        $response = $client->request('POST', 'https://play.railsbank.com/v1/customer/endusers', [
            'body' => '{
                "person": {
                  "name": "'.$user->name.'",
                  "email": "'.$user->email.'",
                  "address": { "address_iso_country": "US" }
                }
            }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        $enduser = json_decode($response->getBody())->enduser_id;
        $response = $client->request('POST', 'https://play.railsbank.com/v1/customer/ledgers', [
            'body' => '{
                "holder_id": "'.$enduser.'",
                "partner_product": "ExampleBank-EUR-1",
                "asset_class": "currency",
                "asset_type": "eur",
                "ledger-type": "ledger-type-single-user",
                "ledger-who-owns-assets": "ledger-assets-owned-by-me",
                "ledger-primary-use-types": ["ledger-primary-use-types-payments"],
                "ledger-t-and-cs-country-of-jurisdiction": "US"
              }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        $ledger = json_decode($response->getBody())->ledger_id;
        $response = $client->request('GET', 'https://play.railsbank.com/v1/customer/ledgers/'.$ledger.'/wait', [
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        $bic_swift = json_decode($response->getBody())->bic_swift;
        $iban = json_decode($response->getBody())->iban;
        $data = New BankAccount();
        $data->user_id = $request->user;
        $data->subbank_id = $request->subbank;
        $data->iban = $iban;
        $data->swift = $bic_swift;
        $data->currency_id = $request->currency;
        $data->save();
        return redirect()->back()->with(array('message' => 'Bank Account has been created successfully'));

        // return response()->json('Bank Account has been created successfully');
        // $response = $client->request('POST', 'https://play.railsbank.com/dev/customer/transactions/receive', [
        //     'body' => '{
        //         "iban": "'.$iban.'",
        //         "bic-swift": "'.$bic_swift.'",
        //         "amount": 10
        //       }'
        //     ,
        //     'headers' => [
        //         'Accept'=> 'application/json',
        //         'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
        //         'Content-Type' => 'application/json',
        //         ],
        // ]);
////////////////////////////// test mode end /////////////////////////////////////////////////////
        // $response = $client->request('POST', 'https://play.railsbank.com/v1/customer/ledgers/'.$ledger.'/external-accounts', [
        //     'body' => '{
        //         "number": "'.$request->banknumber.'",
        //         "bank_code": "'.$request->bankcode.'",
        //         "name": "'.$user->name.'",
        //         "account_type": "routing-number",
        //         "bank_name": "'.$request->bankname.'",
        //         "type": "",
        //         "legal_type": "person",
        //         "external_account_meta": {}
        //       }'
        //     ,
        //     'headers' => [
        //         'Accept'=> 'application/json',
        //         'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
        //         'Content-Type' => 'application/json',
        //         ],
        // ]);
        // $external_account = json_decode($response->getBody())->external_account_id;

        // $response = $client->request('POST','https://play.railsbank.com/v1/customer/beneficiaries', [
        //     'body' => '{
        //         "holder_id": "'.$enduser.'",
        //         "asset_class": "currency",
        //         "asset_type": "eur",
        //         "iban": "'.$subbank->iban.'",
        //         "bic_swift": "'.$subbank->swift.'",
        //         "person": {
        //           "name": "'.$subuser->name.'",
        //           "email": "'.$subuser->email.'",
        //           "address": { "address_iso_country": "US" }
        //         }
        //       }',
        //     'headers' => [
        //         'Accept'=> 'application/json',
        //         'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
        //         'Content-Type' => 'application/json',
        //     ],
        // ]);
        // $beneficiary = json_decode($response->getBody())->beneficiary_id;

        // $response = $client->request('POST', 'https://play.railsbank.com/v1/customer/transactions', [
        //     'body' => '{
        //         "ledger_from_id": "'.$ledger.'",
        //         "beneficiary_id": "'.$beneficiary.'",
        //         "payment_type": "payment-type-EU-SEPA-Step2",
        //         "amount": "'.$request->amount.'"
        //       }',
        //     'headers' => [
        //        'Accept'=> 'application/json',
        //       'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
        //       'Content-Type' => 'application/json',
        //     ],
        //   ]);
        // $transaction = json_decode($response->getBody())->transaction_id;

        // return redirect()->route('user.depositbank.create')->with('success','Deposit amount '.$request->amount.' ('.$currency->code.') successfully!');
    }
}
