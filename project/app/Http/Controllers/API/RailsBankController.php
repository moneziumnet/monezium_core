<?php

namespace App\Http\Controllers\API;


use App\Models\BankGateway;
use App\Models\BankAccount;
use App\Models\Currency;
use App\Models\Charge;
use App\Models\Transaction;
use App\Models\Generalsetting;
use App\Models\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Auth;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Validator;

class RailsBankController extends Controller
{
    public function store(Request $request){
        try {
            $gs = Generalsetting::first();
            $currency = Currency::whereId($request->currency)->first();
            if ($currency->code != 'EUR'){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Sorry, Currently Railsbank API only supports for EUR because this API is not product version.']);
            }

            $client = New Client();
            $user = User::findOrFail($request->user);
            $bankgateway = BankGateway::where('subbank_id', $request->subbank)->first();
            $bankaccount = BankAccount::where('user_id', $request->user)->where('subbank_id', $request->subbank)->where('currency_id', $request->currency)->first();
            if ($bankaccount){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This bank account already exists.']);
            }
            try {
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
            } catch (\Throwable $ex) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $ex->getMessage()]);
            }
            if ($enduser == null) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Sorry, You can\'t create New Bank Account succesfully because RailsBank\'s enduser is not generated by RailsBank API. Please create again.']);
            }


            try {
                $response = $client->request('POST', 'https://play.railsbank.com/v1/customer/ledgers', [
                    'body' => '{
                        "holder_id": "'.$enduser.'",
                        "partner_product": "ExampleBank-'.$currency->code.'-1",
                        "asset_class": "currency",
                        "asset_type": "'.strtolower($currency->code).'",
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
            } catch (\Throwable $ex) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $ex->getMessage()]);
            }
            if ($ledger == null) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Sorry, You can\'t create New Bank Account succesfully because Railsbank\'s ledger is not generated by RailsBank API. Please create again.']);
            }

            try {
                $response = $client->request('GET', 'https://play.railsbank.com/v1/customer/ledgers/'.$ledger.'/wait', [
                    'headers' => [
                        'Accept'=> 'application/json',
                        'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
                        'Content-Type' => 'application/json',
                    ],
                ]);
                $bic_swift = json_decode($response->getBody())->bic_swift;
                $iban = json_decode($response->getBody())->iban;
            } catch (\Throwable $ex) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $ex->getMessage()]);
            }
            if ($iban == null || $bic_swift == null) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Sorry, You can not create New Bank Account succesfully because RailsBank\'s iban or swift is not generated by RailsBank API. Please try again.']);
            }

            $data = New BankAccount();
            $data->user_id = $request->user;
            $data->subbank_id = $request->subbank;
            $data->iban = $iban;
            $data->swift = $bic_swift;
            $data->currency_id = $request->currency;
            $data->save();

            $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
            if(!$chargefee) {
                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
            }

            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $user->id;
            $trans->user_type   = 1;
            $trans->currency_id =  defaultCurr();
            $trans->amount      = 0;
            $trans_wallet       = get_wallet($user->id, defaultCurr(), 1);
            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
            $trans->charge      = $chargefee->data->fixed_charge;
            $trans->type        = '-';
            $trans->remark      = 'account-open';
            $trans->details     = trans('Bank Account Create');
            $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
            $trans->save();

            $currency = Currency::findOrFail(defaultCurr());

            mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'type'=>'Bank', 'date_time'=> dateFormat($trans->created_at)], $user);

            user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
            user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Bank Account has been created successfully']);
            //code...
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }


    }
}
