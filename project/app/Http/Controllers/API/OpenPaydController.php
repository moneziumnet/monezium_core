<?php

namespace App\Http\Controllers\API;


use App\Models\BankGateway;
use App\Models\BankAccount;
use App\Models\Charge;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\Generalsetting;
use App\Models\User;
use App\Models\Country;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Validator;

class OpenPaydController extends Controller
{
    public function store(Request $request){
        try {
            $gs = Generalsetting::first();
            $client = New Client();
            $user = User::findOrFail($request->user);
            $bankgateway = BankGateway::where('subbank_id', $request->subbank)->first();
            $bankaccount = BankAccount::where('user_id', $request->user)->where('subbank_id', $request->subbank)->where('currency_id', $request->currency)->first();
            if ($bankaccount){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This bank account already exists.']);
            }
            try {
                $response = $client->request('POST', 'https://secure-mt.openpayd.com/api/oauth/token?grant_type=client_credentials', [
                    'headers' => [
                       'Accept'=> 'application/json',
                      'Authorization' => 'Basic '.$bankgateway->information->Auth,
                      'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                  ]);
                $res_body = json_decode($response->getBody());
                $auth_token = $res_body->access_token;
                $accounter_id = $res_body->accountHolderId;
            } catch (\Throwable $th) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
            }
            $country = Country::findOrFail($user->country);
            if ($auth_token == null || $accounter_id == null) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Sorry, You can\'t create New Bank Account succesfully because openpayd\'s accounter_id or token is not generated by OPENPAY API. Please create again.']);
            }

            if (!$user->holder_id){
                try {
                    $currency = Currency::whereId($request->currency)->first();

                    if(!isset($user->company_name)) {
                        $body = '{
                            "individual": {
                                "address": {
                                    "addressLine1": "'.$user->address.'",
                                    "city": "'.$user->city.'",
                                    "postCode": "'.$user->zip.'",
                                    "country": "'.$country->iso2.'"
                                },
                                "firstName": "'.explode(" ",$user->name)[0].'",
                                "lastName": "'.explode(" ",$user->name)[1].'",
                                "dateOfBirth": "'.$user->dob.'",
                                "email": "'.$user->email.'"
                            },
                            "clientType": "INDIVIDUAL",
                            "friendlyName": "'.$user->name.'"
                        }';
                    }
                    else {
                        $company_country = Country::findOrFail($user->company_country);

                        $body = '{
                            "company": {
                                "registeredAddress": {
                                    "addressLine1": "'.$user->company_address.'",
                                    "city": "'.$user->company_city.'",
                                    "postCode": "'.$user->company_zipcode.'",
                                    "country": "'.$company_country->iso2.'"
                                },
                                "tradingAddress": {
                                    "addressLine1": "'.$user->company_address.'",
                                    "city": "'.$user->company_city.'",
                                    "postCode": "'.$user->company_zipcode.'",
                                    "country": "'.$company_country->iso2.'"
                                },
                                "companyName": "'.$user->company_name.'",
                                "companyType": "'.$user->company_type.'",
                                "registrationNumber": "'.$user->company_reg_no.'",
                                "contactName": "'.$user->name.'",
                                "email": "'.$user->email.'",
                                "phone": "'.$user->phone.'"
                            },
                            "clientType": "BUSINESS",
                            "friendlyName": "'.$user->company_name.'"
                            }';
                    }
                    $response = $client->request('POST', 'https://secure-mt.openpayd.com/api/linkedClient', [
                        'body' => $body,
                        'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer '.$auth_token,
                        'Content-Type' => 'application/json',
                        'x-account-holder-id' => $accounter_id,
                        ],
                    ]);
                    $res_body = json_decode($response->getBody());
                    $linked_accountid = $res_body->accountHolderId;
                } catch (\Throwable $th) {
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
                }
                $user->holder_id = $linked_accountid;
                $user->update();
            }
            else {
                $linked_accountid = $user->holder_id;
            }
            if ($linked_accountid == null) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Sorry, You can\'t create New Bank Account succesfully because openpayd\'s linked_accountid is not generated by OPENPAY API. Please try again.']);
            }
            try {
                $currency = Currency::whereId($request->currency)->first();
                $response = $client->request('POST', 'https://secure-mt.openpayd.com/api/accounts', [
                    'body' => '{"currency":"'.$currency->code.'","friendlyName":"Billing Account('.$currency->code.')"}',
                    'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$auth_token,
                    'Content-Type' => 'application/json',
                    'x-account-holder-id' => $linked_accountid,
                    ],
                ]);
                $res_body = json_decode($response->getBody());
                $internal_id = $res_body->internalAccountId;
            } catch (\Throwable $th) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
            }
            if ($internal_id == null) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Sorry, You can\'t create New Bank Account succesfully because openpayd\'s internal_id is not generated by OPENPAY API. Please try again.']);
            }

            try {
                $response = $client->request('GET', 'https://secure-mt.openpayd.com/api/bank-accounts?internalAccountId='.$internal_id, [
                    'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$auth_token,
                    'Content-Type' => 'application/json',
                    'x-account-holder-id' => $linked_accountid,
                    ],
                ]);
                $res_body = json_decode($response->getBody())[0];

                $bic_swift = $res_body->bic;
                $iban = $res_body->iban;
            } catch (\Throwable $th) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
            }


            if ($iban == null || $bic_swift == null || $iban == '' || $bic_swift == '' ) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Sorry, You can\'t create New Bank Account succesfully because openpayd\'s Iban and Swift code are not generated by OPENPAY API. Please try again.']);
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
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Bank Account has been created successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }
}