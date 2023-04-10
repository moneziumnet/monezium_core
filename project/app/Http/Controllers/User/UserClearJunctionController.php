<?php

namespace App\Http\Controllers\User;

use App\Models\BankPlan;
use App\Models\Currency;
use App\Models\Beneficiary;
use App\Models\BankGateway;
use App\Models\User;
use App\Models\Charge;
use App\Models\BankAccount;
use App\Models\Generalsetting;
use App\Models\Country;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\BalanceTransfer;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use DateTime;

class UserClearJunctionController extends Controller
{
    // private $url = 'https://sandbox.clearjunction.com/v7/'; // Sandbox Host Url
    private $url = 'https://client.clearjunction.com/v7/'; // Host Url
    private $wallet_uuid = '8f2980e8-d67b-4bbf-9622-b7310444fac9'; // Sandbwallet_uuid
    private $API_Key = '93030547-8be2-4895-afc7-fa9eda34937e';
    private $apiPassword = 'awklgvvhhb4g';
    private $key = '{"API_Key":"93030547-8be2-4895-afc7-fa9eda34937e","api_password":"awklgvvhhb4g","wallet_uuid":"8f2980e8-d67b-4bbf-9622-b7310444fac9"}';

	//echo $hashed = hash("sha512", $password);
    public function getToken($request, $subbank) {
        $bankgateway = BankGateway::where('subbank_id', $subbank)->first();
        $secret = hash('sha512', $bankgateway->information->api_password);
        $datetime = new DateTime();
        $now = $datetime->format(DateTime::ATOM);
        // $body = json_encode($request);
        $signature = hash('sha512', mb_strtoupper($bankgateway->information->API_Key).$now.mb_strtoupper($secret).mb_strtoupper($request));
        return array($signature, $now);
    }

    public function CheckBankWallet($subbank) {
        $client = new  Client();
        $bankgateway = BankGateway::where('subbank_id', $subbank)->first();
        $param = $this->getToken('{}', $subbank);
        // dd($param);
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
      $currency = Currency::whereId($request->currency)->first();
      $gs = Generalsetting::first();
      if ($currency->code != 'EUR'){
        return redirect()->back()->with(array('warning' => 'Sorry, Currently Clear Junction API only supports for EUR.'));
      }

        $client = New Client();
        $user = User::findOrFail($request->user);
        $bankgateway = BankGateway::where('subbank_id', $request->subbank)->first();
        $banklastindex = BankAccount::orderBy('id', 'DESC')->first()->id + rand(100000,999999);
        $bankaccount = BankAccount::where('user_id', $request->user)->where('subbank_id', $request->subbank)->where('currency_id', $request->currency)->first();
        $country = Country::findOrFail($user->country);

        if ($bankaccount){
            return redirect()->back()->with(array('warning' => 'This bank account already exists.'));

        }
        if(!isset($user->company_name)) {
          if(!($user->phone)){
              return redirect()->back()->with(array('warning' => 'Please input phone number in User Profile.'));
          }
          if(!($user->dob)){
              return redirect()->back()->with(array('warning' => 'Please input birthday in User Profile.'));
          }
          if(!($country)){
              return redirect()->back()->with(array('warning' => 'Please select country in User Profile.'));
          }
          if(!($user->zip)){
              return redirect()->back()->with(array('warning' => 'Please input zipcode in User Profile.'));
          }
          if(!($user->city)){
              return redirect()->back()->with(array('warning' => 'Please input City name in User Profile.'));
          }
          if(!($user->address)){
              return redirect()->back()->with(array('warning' => 'Please input Address in User Profile.'));
          }
          if(!($user->your_id)){
              return redirect()->back()->with(array('warning' => 'Please input ID number in User Profile.'));
          }
          if(!($user->issued_authority)){
              return redirect()->back()->with(array('warning' => 'Please input Provider Authority Name in User Profile.'));
          }
          if(!($user->date_of_issue)){
              return redirect()->back()->with(array('warning' => 'Please input Issued Date in User Profile.'));
          }
          if(!($user->date_of_expire)){
              return redirect()->back()->with(array('warning' => 'Please input Expire Date in User Profile.'));
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
            // dd(json_encode($body));
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
                //   dd($response);
                //   return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
            } catch (\Throwable $th) {
                return redirect()->back()->with(array('warning' => json_encode($th->getMessage())));

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
                //   dd($response);
                //   return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
            } catch (\Throwable $th) {
                return redirect()->back()->with(array('warning' => json_encode($th->getMessage())));
            }

            $res = $this->CheckBankWallet($request->subbank);
            // dd($orderid);
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
            send_notification($user->id, 'New Bank Wallet Created for '.($user->company_name ?? $user->name).'. Please check .', route('admin-user-banks', $user->id));

            user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
            user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
            return redirect()->back()->with(array('message' => 'Bank Account has been created successfully'));

        }
        else {
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
            send_notification($user->id, 'New Bank Wallet Created for '.($user->company_name ?? $user->name).'. Please check .', route('admin-user-banks', $user->id));
            user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
            user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
            return redirect()->back()->with(array('message' => 'Bank Account has been created successfully'));
        }
    }

    public function ClientWalletBalance(Request $request) {
        $client = new  Client();
        $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
        $param = $this->getToken(json_encode($request->all()));
        $response = $client->request('GET',  $this->url.'bank/wallets/'.$bankgateway->information->wallet_uuid.'?returnPaymentMethods=true', [
            'body' => json_encode($request->all()),
            'headers' => [
               'Accept'=> '*/*',
              'X-API-KEY' => $bankgateway->information->API_Key,
              'Authorization' => 'Bearer '.$param[0],
              'Date' => $param[1],
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function CreateInvidualWallet(Request $request) {
        $client = new  Client();
        $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
        $param = $this->getToken(json_encode($request->all()));
        $response = $client->request('POST',  $this->url.'bank/wallets/'.$bankgateway->information->wallet_uuid.'/individuals', [
            'body' => json_encode($request->all()),
            'headers' => [
               'Accept'=> '*/*',
              'X-API-KEY' => $bankgateway->information->API_Key,
              'Authorization' => 'Bearer '.$param[0],
              'Date' => $param[1],
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function WalletTransfer(Request $request) {
        $client = new  Client();
        $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
        $param = $this->getToken(json_encode($request->all()));
        $response = $client->request('POST',  $this->url.'gate/wallets/transfer', [
            'body' => json_encode($request->all()),
            'headers' => [
               'Accept'=> '*/*',
              'X-API-KEY' => $bankgateway->information->API_Key,
              'Authorization' => 'Bearer '.$param[0],
              'Date' => $param[1],
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetWalletStatement(Request $request) {
        $client = new  Client();
        $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
        $param = $this->getToken(json_encode($request->all()));
        $response = $client->request('POST',  $this->url.'gate/wallets/statement', [
            'body' => json_encode($request->all()),
            'headers' => [
               'Accept'=> '*/*',
              'X-API-KEY' => $bankgateway->information->API_Key,
              'Authorization' => 'Bearer '.$param[0],
              'Date' => $param[1],
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function AllocateIbanIndividual(Request $request) {
        $client = new  Client();
        $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
        $param = $this->getToken(json_encode($request->all()));
        $response = $client->request('POST',  $this->url.'gate/iban/individual', [
            'body' => json_encode($request->all()),
            'headers' => [
               'Accept'=> '*/*',
              'X-API-KEY' => $bankgateway->information->API_Key,
              'Authorization' => 'Bearer '.$param[0],
              'Date' => $param[1],
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }



    public function AllocateBecsCreate(Request $request) {
        $client = new  Client();
        $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
        $param = $this->getToken(json_encode($request->all()));
        $response = $client->request('POST',  $this->url.'gate/allocate/v2/create/becs', [
            'body' => json_encode($request->all()),
            'headers' => [
               'Accept'=> '*/*',
              'X-API-KEY' => $bankgateway->information->API_Key,
              'Authorization' => 'Bearer '.$param[0],
              'Date' => $param[1],
              'Content-Type' => 'application/json',
            ],
          ]);
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
    }

    public function GetIbanStatusByClientOrder(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/allocate/v2/status/iban/clientOrder/'.$request->get('client_order_id'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function GetIbanStatusByOrderRef(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/allocate/v2/status/iban/orderReference/'.$request->get('order_ref'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function GetIbanByIban(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/allocate/v2/info/iban/'.$request->get('iban_no'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function IbanListByCid(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/allocate/v2/list/iban/'.$request->get('customer_id'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function BecsByOrderRef(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/allocate/v2/status/becs/orderReference/'.$request->get('order_ref'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function BecsByClientOrderid(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/allocate/v2/status/becs/clientOrder/'.$request->get('client_order_id'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  /*Payout Clear Junction API*/
  public function ToUsBankSwift(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/bankTransfer/swift?checkOnly=true', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function ToUsBankFedwire(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/bankTransfer/fedwire?checkOnly=true', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function ToUsBankSignet(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/signet?checkOnly=true', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function InternalPayment(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/internalPayment', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function EuBankInstant(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/bankTransfer/sepaInst?checkOnly=true', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function UkBankFaster(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/bankTransfer/fps?checkOnly=true', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function UkBankChaps(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/bankTransfer/chaps?checkOnly=true', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function AuBankBecs(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/bankTransfer/becs?checkOnly=ture', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function UaBankPayout(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/bankTransfer/ua?checkOnly=ture', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function MdBankPayout(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/bankTransfer/md?checkOnly=ture', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function CreditCardPayout(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/payout/creditCardNonPc?checkOnly=ture', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function PayoutStatusOrderRef(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      //96ef417b-b026-4684-9873-77333a3712f7
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/status/payout/orderReference/'.$request->input('order_ref'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function PayoutStatusClientOrder(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      //96ef417b-b026-4684-9873-77333a3712f7
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/status/payout/clientOrder/'.$request->input('client_order'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function CreateInvoice(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      //96ef417b-b026-4684-9873-77333a3712f7
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/invoice/creditCard', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function InvoiceStatusByRef(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      //96ef417b-b026-4684-9873-77333a3712f7
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/status/invoice/orderReference/'.$request->input('order_ref'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function InvoiceStatusByOrder(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      //96ef417b-b026-4684-9873-77333a3712f7
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/status/invoice/clientOrder/'.$request->input('client_order_id'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function TransactionApprove(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      //96ef417b-b026-4684-9873-77333a3712f7
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/transactionAction/approve', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function TransactionCancel(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      //96ef417b-b026-4684-9873-77333a3712f7
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/transactionAction/cancel', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function CreateToken(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      //96ef417b-b026-4684-9873-77333a3712f7
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'pci/createToken', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function CheckRequiByIBAN(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      //iban_no = ES9121000418450200051332
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/checkRequisite/bankTransfer/eu/iban'.$request->input('iban_no'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function TransactionReport(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();

      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/reports/transactionReport', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function TransferStatusOrderRef(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      //iban_no = ES9121000418450200051332
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/status/walletTransfer/orderReference/'.$request->input('order_ref'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function TransferStatusClientOrder(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      //iban_no = ES9121000418450200051332
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/status/walletTransfer/clientOrder/'.$request->input('client_order_id'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  //Reserve Individual Wallet
  public function ReserveIndividualWallet(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/wallets/individual', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }


  public function ReserveCorporateWallet(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('POST',  $this->url.'gate/wallets/corporate', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function ReserveStatusByOrderRef(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/wallets/status/orderReference/'.$request->input('order_ref'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function ReserveStatusByClientOrderID(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'gate/wallets/status/clientOrder/'.$request->input('client_order_id'), [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  // Entitty Partner
  public function CorporateEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'CorporateEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function CorporateUaEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'CorporateUaEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function IndividualBecsEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'IndividualBecsEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function IndividualInternalPaymentEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'IndividualInternalPaymentEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function CorporateBecsEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'CorporateBecsEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function IndividualUsEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'IndividualUsEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function IndividualEuEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'IndividualEuEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function IndividualMdEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'IndividualMdEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }
  // Entity Payment Details
  public function BankTransferSwiftPaymentDetailEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'BankTransferSwiftPaymentDetailEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function BankTransferFedwirePaymentDetailEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'BankTransferFedwirePaymentDetailEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function SignetPaymentDetailEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'SignetPaymentDetailEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function InternalPaymentDetailEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'InternalPaymentDetailEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function BankTransferSepaInstPaymentDetailEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'BankTransferSepaInstPaymentDetailEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function BankTransferUkFpsPaymentDetailEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'BankTransferUkFpsPaymentDetailEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function BankTransferUkChapsPaymentDetailEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'BankTransferUkChapsPaymentDetailEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function BankTransferUkBacsPaymentDetailEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'BankTransferUkBacsPaymentDetailEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function BankTransferUkDefaultPaymentDetailEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'BankTransferUkDefaultPaymentDetailEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function BankTransferBecsPaymentDetailEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'BankTransferBecsPaymentDetailEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function BankTransferMdPaymentDetailEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'BankTransferMdPaymentDetailEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function BankTransferUaPaymentDetailEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'BankTransferUaPaymentDetailEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function CreditCardPaymentDetailEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'CreditCardPaymentDetailEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  //Entity Registrants

  public function AllocateIbanIndividualEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'AllocateIbanIndividualEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function AllocateIbanCorporateEntity(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'AllocateIbanCorporateEntity', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function AllocateBecsIndividual(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'AllocateBecsIndividual', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

  public function AllocateBecsCorporate(Request $request) {
      $client = new  Client();
      $bankgateway = BankGateway::where('subbank_id', $request->subid)->first();
      $param = $this->getToken(json_encode($request->all()));
      $response = $client->request('GET',  $this->url.'AllocateBecsCorporate', [
          'body' => json_encode($request->all()),
          'headers' => [
             'Accept'=> '*/*',
            'X-API-KEY' => $bankgateway->information->API_Key,
            'Authorization' => 'Bearer '.$param[0],
            'Date' => $param[1],
            'Content-Type' => 'application/json',
          ],
        ]);
      return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => json_decode($response->getBody())]);
  }

}
