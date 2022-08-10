<?php

namespace App\Http\Controllers\Deposit;

use Illuminate\Support\Str;
use App\Classes\GeniusMailer;
use App\Models\BankGateway;
use App\Models\BankAccount;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;

class OpenPaydController extends Controller
{
    public function store(Request $request){
        $client = New Client();
        $user = User::findOrFail($request->user);
        $bankgateway = BankGateway::where('subbank_id', $request->subbank)->first();
        $bankaccount = BankAccount::where('user_id', $request->user)->where('subbank_id', $request->subbank)->where('currency_id', $request->currency)->first();
        if ($bankaccount){
            return redirect()->back()->with(array('warning' => 'This bank account already exists.'));

        }

        $response = $client->request('POST', 'https://sandbox.openpayd.com/api/oauth/token?grant_type=client_credentials', [
            'headers' => [
               'Accept'=> 'application/json',
              'Authorization' => 'Basic '.$bankgateway->information->Auth,
              'Content-Type' => 'application/x-www-form-urlencoded',
            ],
          ]);
        $res_body = json_decode($response->getBody());
        $auth_token = $res_body->access_token;
        $accounter_id = $res_body->accountHolderId;
        $currency = Currency::whereId($request->currency)->first();
        $response = $client->request('POST', 'https://sandbox.openpayd.com/api/accounts', [
            'body' => '{"currency":"'.$currency->code.'","friendlyName":"Billing Account('.$currency->code.')"}',
            'headers' => [
              'Accept' => 'application/json',
              'Authorization' => 'Bearer '.$auth_token,
              'Content-Type' => 'application/json',
              'x-account-holder-id' => $accounter_id,
            ],
        ]);
        $res_body = json_decode($response->getBody());
        $internal_id = $res_body->internalAccountId;
        $response = $client->request('GET', 'https://sandbox.openpayd.com/api/bank-accounts?internalAccountId='.$internal_id, [
            'headers' => [
              'Accept' => 'application/json',
              'Authorization' => 'Bearer '.$auth_token,
              'Content-Type' => 'application/json',
              'x-account-holder-id' => $accounter_id,
            ],
        ]);
        $res_body = json_decode($response->getBody())[0];

        $bic_swift = $res_body->bic;
        $iban = $res_body->iban;
        $data = New BankAccount();
        $data->user_id = $request->user;
        $data->subbank_id = $request->subbank;
        $data->iban = $iban;
        $data->swift = $bic_swift;
        $data->currency_id = $request->currency;
        $data->save();
        return redirect()->back()->with(array('message' => 'Bank Account has been created successfully'));

    }
}
