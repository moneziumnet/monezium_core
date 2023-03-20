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

class ClearJunctionController extends Controller
{
    private $url = 'https://client.clearjunction.com/v7/'; // Host Url

    public function getToken($request, $subbank) {
        $bankgateway = BankGateway::where('subbank_id', $subbank)->first();
        $secret = hash('sha512', $bankgateway->information->api_password);
        $datetime = new DateTime();
        $now = $datetime->format(DateTime::ATOM);
        $signature = hash('sha512', mb_strtoupper($bankgateway->information->API_Key).$now.mb_strtoupper($secret).mb_strtoupper($request));
        return array($signature, $now);
    }

    public function CheckBankWallet($subbank) {
        $client = new  Client();
        $bankgateway = BankGateway::where('subbank_id', $subbank)->first();
        $param = $this->getToken('{}', $subbank);
        $response = $client->request('GET',  $this->url.'bank/wallets/'.$bankgateway->information->wallet_uuid.'?returnPaymentMethods=true', [
            'body' => '{}',
            'headers' => [
               'Accept'=> '*/*',
              'X-API-KEY' => $bankgateway->information->API_Key,
              'Authorization' => 'Bearer '.$param[0],
              'Date' => $param[1],
              'Content-Type' => 'application/json',
            ],
          ]);
          $res_body = json_decode($response->getBody());
        return $res_body;
    }

    public function AllocateIbanCreate(Request $request) {
        try {
            $currency = Currency::whereId($request->currency)->first();
            $gs = Generalsetting::first();
            if ($currency->code != 'EUR'){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Sorry, Currently Clear Junction API only supports for EUR.']);
            }

              $client = New Client();
              $user = User::findOrFail($request->user);
              $bankgateway = BankGateway::where('subbank_id', $request->subbank)->first();
              $banklastindex = BankAccount::orderBy('id', 'DESC')->first()->id + rand(100000,999999);
              $bankaccount = BankAccount::where('user_id', $request->user)->where('subbank_id', $request->subbank)->where('currency_id', $request->currency)->first();
              $country = Country::findOrFail($user->country);

            if ($bankaccount){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This bank account already exists.']);
            }

            if(!isset($user->company_name)) {
                if(!($user->phone)){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Please input phone number in User Profile.']);
                }
                if(!($user->dob)){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Please input birthday in User Profile.']);
                }
                if(!($country)){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Please select country in User Profile.']);
                }
                if(!($user->zip)){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Please input zipcode in User Profile.']);
                }
                if(!($user->city)){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Please input City name in User Profile.']);
                }
                if(!($user->address)){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Please input Address in User Profile.']);
                }
                if(!($user->your_id)){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Please input ID number in User Profile.']);
                }
                if(!($user->issued_authority)){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Please input Provider Authority Name in User Profile.']);
                }
                if(!($user->date_of_issue)){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Please input Issued Date in User Profile.']);
                }
                if(!($user->date_of_expire)){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Please input Expire Date in User Profile.']);
                }
                $body = '{
                  "clientOrder": "'.$banklastindex.'",
                    "walletUuid": "'.$bankgateway->information->wallet_uuid.'",
                    "ibansGroup": "DEFAULT",
                    "ibanCountry": "GB",
                    "registrant": {
                      "clientCustomerId": "'.$banklastindex.'",
                      "individual": {
                        "phone": "+'.$user->phone.'",
                        "email": "'.$user->email.'",
                        "birthDate": "'.$user->dob.'",
                        "address": {
                          "country": "'.$country->iso2.'",
                          "zip": "'.$user->zip.'",
                          "city": "'.$user->city.'",
                          "street": "'.$user->address.'"
                        },
                        "document": {
                          "type": "passport",
                          "number": "'.$user->your_id.'",
                          "issuedCountryCode": "'.$country->iso2.'",
                          "issuedBy": "'.$user->issued_authority.'",
                          "issuedDate": "'.$user->date_of_issue.'",
                          "expirationDate": "'.$user->date_of_expire.'"
                        },
                        "lastName": "'.explode(" ",$user->name)[1].'",
                        "firstName": "'.explode(" ",$user->name)[0].'"
                      }
                    }
                  }';
                $param = $this->getToken($body, $request->subbank);
                try {
                    $response = $client->request('POST',  $this->url.'gate/allocate/v2/create/iban', [
                        'body' => $body,
                        'headers' => [
                            'Accept'=> '*/*',
                        'X-API-KEY' => $bankgateway->information->API_Key,
                        'Authorization' => 'Bearer '.$param[0],
                        'Date' => $param[1],
                        'Content-Type' => 'application/json',
                        ],
                    ]);
                    $res_body = json_decode($response->getBody());
                    $orderid = $res_body->clientOrder;
                } catch (\Throwable $th) {
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
                }
                sleep(20);
                $param = $this->getToken('{}', $request->subbank);
                try {
                    $response = $client->request('GET',  $this->url.'gate/allocate/v2/status/iban/clientOrder/'.$orderid, [
                        'body' => '{}',
                        'headers' => [
                            'Accept'=> '*/*',
                        'X-API-KEY' => $bankgateway->information->API_Key,
                        'Authorization' => 'Bearer '.$param[0],
                        'Date' => $param[1],
                        'Content-Type' => 'application/json',
                        ],
                    ]);
                    $res_body = json_decode($response->getBody());
                    $iban = $res_body->iban;
                } catch (\Throwable $th) {
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
                }

                $res = $this->CheckBankWallet($request->subbank);
                $bic_swift = 'CLJUGB21';
                foreach ($res->paymentMethods as $key => $value) {
                    if ($value->accountNumber == $iban) {
                        $bic_swift = $value->bankCode;
                    }
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

                $def_currency = Currency::findOrFail(defaultCurr());
                mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $def_currency->code, 'type' => 'Bank', 'date_time'=> dateFormat($trans->created_at)], $user);

                user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
                user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Bank Account has been created successfully']);
            } else {
                $data = New BankAccount();
                $data->user_id = $request->user;
                $data->subbank_id = $request->subbank;
                $data->iban = '';
                $data->swift = '';
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

                $def_currency = Currency::findOrFail(defaultCurr());
                mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $def_currency->code, 'type' => 'Bank', 'date_time'=> dateFormat($trans->created_at)], $user);

                user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
                user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Bank Account has been created successfully']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
      }
}
