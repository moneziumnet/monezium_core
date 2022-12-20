<?php

namespace App\Http\Controllers\User;

use App\Models\BankPlan;
use App\Models\PlanDetail;
use App\Models\Currency;
use App\Models\BankAccount;
use App\Models\SubInsBank;
use App\Models\Beneficiary;
use App\Models\Transaction;
use App\Models\BankGateway;
use App\Models\BankPoolAccount;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Classes\GoogleAuthenticator;
use App\Models\Generalsetting;
use App\Models\BalanceTransfer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use DateTime;

class OtherBankController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function getToken($request, $subbank) {
        $bankgateway = BankGateway::where('subbank_id', $subbank)->first();
        $secret = hash('sha512', $bankgateway->information->api_password);
        $datetime = new DateTime();
        $now = $datetime->format(DateTime::ATOM);
        // $body = json_encode($request);
        $signature = hash('sha512', mb_strtoupper($bankgateway->information->API_Key).$now.mb_strtoupper($secret).mb_strtoupper($request));
        return array($signature, $now);
    }
    
    public function othersend($id){
        $data['bankaccounts'] = BankAccount::whereUserId(auth()->id())->pluck('subbank_id');
        $data['banks'] = SubInsBank::where('status', 1)->get();
        $data['data'] = Beneficiary::findOrFail($id);
        $data['other_bank_limit'] = Generalsetting::first()->other_bank_limit;
        $data['user'] = auth()->user();
        return view('user.otherbank.send',$data);
    }

    public function copysend($id){
        $data['beneficiary'] = BalanceTransfer::findOrFail($id);
        $data['bankaccounts'] = BankAccount::whereUserId(auth()->id())->pluck('subbank_id');
        $data['banks'] = SubInsBank::whereIn('id', $data['bankaccounts'])->get();
        $data['data'] = Beneficiary::findOrFail($data['beneficiary']->beneficiary_id);
        $data['other_bank_limit'] = Generalsetting::first()->other_bank_limit;
        $data['user'] = auth()->user();


        return view('user.otherbank.copy',$data);
    }

    public function store(Request $request){
        $user = auth()->user();
        if($user->paymentCheck('External Payments')) {
            if ($user->payment_fa != 'two_fa_google') {
                if ($user->two_fa_code != $request->otp) {
                    return redirect()->back()->with('unsuccess','Verification code is not matched.');
                }
            }
            else{
                $googleAuth = new GoogleAuthenticator();
                $secret = $user->go;
                $oneCode = $googleAuth->getCode($secret);
                if ($oneCode != $request->otp) {
                    return redirect()->back()->with('unsuccess','Verification code is not matched.');
                }
            }
        }
        $other_bank_limit =Generalsetting::first()->other_bank_limit;
        if ($request->amount >= $other_bank_limit) {
            $rules = [
                'document' => 'required|mimes:xls,xlsx,pdf,jpg,png'
            ];
        }
        else {
            $rules = [
                'document' => 'mimes:xls,xlsx,pdf,jpg,png'
            ];
        }


        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with('unsuccess',$validator->getMessageBag()->toArray()['document'][0]);
        }


        if($user->bank_plan_id === null){
            return redirect()->back()->with('unsuccess','You have to buy a plan to withdraw.');
        }

        if(now()->gt($user->plan_end_date)){
            return redirect()->back()->with('unsuccess','Plan Date Expired.');
        }

        $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'withdraw')->first();
        $dailySend = BalanceTransfer::whereUserId(auth()->id())->whereDate('created_at', '=', date('Y-m-d'))->whereStatus(1)->sum('amount');
        $monthlySend = BalanceTransfer::whereUserId(auth()->id())->whereMonth('created_at', '=', date('m'))->whereStatus(1)->sum('amount');

        if($dailySend > $global_range->daily_limit){
            return redirect()->back()->with('unsuccess','Daily send limit over.');
        }

        if($monthlySend > $global_range->monthly_limit){
            return redirect()->back()->with('unsuccess','Monthly send limit over.');
        }


        $gs = Generalsetting::first();

        $dailyTransactions = BalanceTransfer::whereType('other')->whereUserId(auth()->user()->id)->whereDate('created_at', now())->get();
        $monthlyTransactions = BalanceTransfer::whereType('other')->whereUserId(auth()->user()->id)->whereMonth('created_at', now()->month())->get();
        $transaction_global_cost = 0;
        $currency = Currency::findOrFail($request->currency_id);
        $rate = getRate($currency);
        $transaction_global_fee = check_global_transaction_fee($request->amount/$rate, $user, 'withdraw');

        if ($global_range) {
            if($transaction_global_fee)
            {
                $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/($rate*100)) * $transaction_global_fee->data->percent_charge;
            }
            $finalAmount = $request->amount - $transaction_global_cost*$rate;

            if($global_range->min > $request->amount/$rate){
                return redirect()->back()->with('unsuccess','Request Amount should be greater than this '.$global_range->min);
            }

            if($global_range->max < $request->amount/$rate){
                return redirect()->back()->with('unsuccess','Request Amount should be less than this '.$global_range->max);
            }

            $balance = user_wallet_balance(auth()->id(), $request->currency_id);

            if($balance<0 || $finalAmount > $balance){
                return redirect()->back()->with('unsuccess','Insufficient Balance!');
            }

            if($global_range->daily_limit <= $finalAmount){
                return redirect()->back()->with('unsuccess','Your daily limitation of transaction is over.');
            }

            if($global_range->daily_limit <= $dailyTransactions->sum('final_amount')){
                return redirect()->back()->with('unsuccess','Your daily limitation of transaction is over.');
            }


            if($global_range->monthly_limit < $monthlyTransactions->sum('final_amount')){
                return redirect()->back()->with('unsuccess','Your monthly limitation of transaction is over.');
            }



            $customer_bank = BankAccount::whereUserId($user->id)->where('subbank_id',$request->subbank)->where('currency_id', $currency->id)->first();
            $bankgateway = BankGateway::where('subbank_id', $request->subbank)->first();
            $master_account = BankPoolAccount::where('bank_id', $request->subbank)->where('currency_id', $currency->id)->first();
    
            $subbank = SubInsBank::where('id', $request->subbank)->first();
            $client = New Client();
            $msg = __('Status Updated Successfully.');
            $paybeneficiary = Beneficiary::findOrFail($request->beneficiary_id);

            if($subbank->hasGateway()){
                if($bankgateway->keyword == 'openpayd') {
    
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
                        return redirect()->back()->with('unsuccess',$th->getMessage());
                }
    
    
                    try {
                        $response = $client->request('GET', 'https://secure-mt.openpayd.com/api/accounts?iban='.$customer_bank->iban, [
                            'headers' => [
                            'Accept' => 'application/json',
                            'Authorization' => 'Bearer '.$auth_token,
                            'Content-Type' => 'application/json',
                            'x-account-holder-id' => $user->holder_id,
                            ],
                        ]);
                        $res_body = json_decode($response->getBody())->content[0];
    
                        $account_id = $res_body->id;
                        $amount = $res_body->availableBalance->value;
                    } catch (\Throwable $th) {
                    return redirect()->back()->with('unsuccess',$th->getMessage());
                    }
    
                    try {
                        $response = $client->request('GET', 'https://secure-mt.openpayd.com/api/accounts?iban='.$master_account->iban, [
                            'headers' => [
                            'Accept' => 'application/json',
                            'Authorization' => 'Bearer '.$auth_token,
                            'Content-Type' => 'application/json',
                            'x-account-holder-id' => $accounter_id,
                            ],
                        ]);
                        $res_body = json_decode($response->getBody())->content[0];
    
                        $master_account_id = $res_body->id;
                        $master_amount = $res_body->availableBalance->value;
                        if ($master_amount < $request->amount) {
                            return redirect()->back()->with('unsuccess','Your balance is Insufficient');

                        }
                    } catch (\Throwable $th) {
                        return redirect()->back()->with('unsuccess',$th->getMessage());
                    }
    
                    try {
                        $customer_name = $paybeneficiary->type == 'RETAIL' ? '"firstName":"'.explode(" ",$paybeneficiary->name, 2)[0].'","lastName":"'.explode(" ",$paybeneficiary->name, 2)[1].'",' : '"companyName":"'.$paybeneficiary->name.'",';
                        $response = $client->request('POST', 'https://secure-mt.openpayd.com/api/transactions/sweepPayout', [
                            'body' =>
                                '{"beneficiary":
                                    {"bankAccountCountry":"'.substr($request->account_iban, 0,2).'",
                                    "customerType":"'.$paybeneficiary->type.'",
                                    '.$customer_name.'
                                    "iban":"'.$request->account_iban.'",
                                    "bic":"'.$request->swift_bic.'"
                                    },
                                "amount":
                                    {"value":"'.$request->amount.'",
                                    "currency":"'.$currency->code.'"
                                    },
                                "linkedAccountHolderId":"'.$user->holder_id.'",
                                "accountId":"'.$account_id.'",
                                "sweepSourceAccountId":"'.$master_account_id.'",
                                "paymentType":"'.$request->payment_type.'",
                                "reference":"'.$request->des.'"
                                }',
                            'headers' => [
                            'Accept' => 'application/json',
                            'Authorization' => 'Bearer '.$auth_token,
                            'Content-Type' => 'application/json',
                            'x-account-holder-id' => $accounter_id,
                            ],
                        ]);
                        $res_body = json_decode($response->getBody());
                        $transaction_id = $res_body->transactionId  ;
                    } catch (\Throwable $th) {
                    return redirect()->back()->with('unsuccess',$th->getMessage());
                }
                }
                else if($bankgateway->keyword == 'railsbank') {
    
                    try {
                        $response = $client->request('GET','https://play.railsbank.com/v1/customer/ledgers?account_number='.$customer_bank->iban, [
                            'headers' => [
                                'Accept'=> 'application/json',
                                'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
                                'Content-Type' => 'application/json',
                            ],
                        ]);
                        $enduser = json_decode($response->getBody())[0]->holder_id;
                        $amount = json_decode($response->getBody())[0]->amount;
                        $ledger = json_decode($response->getBody())[0]->ledger_id;
                        if ($amount < $request->amount) {
                            return redirect()->back()->with(array('warning' => 'Insufficient Balance.'));
                        }
                    } catch (\Throwable $th) {
                        return redirect()->back()->with('unsuccess',$th->getMessage());
                    }
                    try {
    
                        $response = $client->request('POST','https://play.railsbank.com/v1/customer/beneficiaries', [
                            'body' => '{
                                "holder_id": "'.$enduser.'",
                                "asset_class": "currency",
                                "asset_type": "eur",
                                "iban": "'.$request->account_iban.'",
                                "bic_swift": "'.$request->swift_bic.'",
                                "person": {
                                "name": "'.$paybeneficiary->name.'",
                                "email": "'.$paybeneficiary->email.'",
                                "address": { "address_iso_country": "'.substr($request->account_iban, 0,2).'" }
                                }
                            }',
                            'headers' => [
                                'Accept'=> 'application/json',
                                'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
                                'Content-Type' => 'application/json',
                            ],
                        ]);
    
                        $beneficiary = json_decode($response->getBody())->beneficiary_id;
                    } catch (\Throwable $th) {
                        return redirect()->back()->with('unsuccess',$th->getMessage());
                    }
                    try {
                        $response = $client->request('POST', 'https://play.railsbank.com/v1/customer/transactions', [
                            'body' => '{
                                "ledger_from_id": "'.$ledger.'",
                                "beneficiary_id": "'.$beneficiary.'",
                                "payment_type": "payment-type-EU-SEPA-Step2",
                                "amount": "'.$request->amount.'"
                            }',
                            'headers' => [
                            'Accept'=> 'application/json',
                            'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
                            'Content-Type' => 'application/json',
                            ],
                        ]);
                        $transaction_id = json_decode($response->getBody())->transaction_id;
                    } catch (\Throwable $th) {
                        return redirect()->back()->with('unsuccess',$th->getMessage());
                    }
                }
                else if($bankgateway->keyword == 'clearjunction') {
                    $clientorder = rand(1000000, 9999999);
                    $type =   $paybeneficiary->type == 'RETAIL' ? "individual" : "corporate";
                    $payee_name = $paybeneficiary->type == 'RETAIL' ? '"firstName":"'.explode(" ",$paybeneficiary->name, 2)[0].'","lastName":"'.explode(" ",$paybeneficiary->name, 2)[1].'"' : '"name":"'.$paybeneficiary->name.'"';
                    $body = '{
                        "clientOrder": "'.$clientorder.'",
                        "currency": "'.$currency->code.'",
                        "amount": '.$request->amount.',
                        "description": "'.$request->des.'",
                        "payee": {
                          '.$type.': {
                            '.$payee_name.'
                          }
                        },
                        "payeeRequisite": {
                          "iban": "'.$request->account_iban.'",
                          "bankSwiftCode": "'.$request->swift_bic.'"
                        },
                        "payerRequisite": {
                          "iban": "'.$customer_bank->iban.'",
                          "bankSwiftCode": "'.$customer_bank->swift.'"
                        }
                      }';
                      $param = $this->getToken($body, $request->subbank);
    
                      try {
                        $response = $client->request('POST',  'https://client.clearjunction.com/v7/gate/payout/bankTransfer/eu?checkOnly=false', [
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
    
                          $transaction_id = $res_body->requestReference;
                    } catch (\Throwable $th) {
                        return redirect()->back()->with('unsuccess',$th->getMessage());
                    }
                }
                else if($bankgateway->keyword == 'swan') {
                    try {
                        $options = [
                                'multipart' => [
                                [
                                    'name' => 'client_id',
                                    'contents' => $bankgateway->information->client_id
                                ],
                                [
                                    'name' => 'client_secret',
                                    'contents' => $bankgateway->information->client_secret
                                ],
                                [
                                    'name' => 'grant_type',
                                    'contents' => 'client_credentials'
                                ]
                            ]];
                            $response = $client->request('POST', 'https://oauth.swan.io/oauth2/token', $options);
                            $res_body = json_decode($response->getBody());
                            $access_token = $res_body->access_token;
                    } catch (\Throwable $th) {
                        return redirect()->back()->with('unsuccess',$th->getMessage());
                    }
                    try {
                        $body = '{"query":"query MyQuery {\\n  accounts(filters: {}) {\\n    edges {\\n      node {\\n        id\\n        BIC\\n        IBAN\\n      }\\n    }\\n  }\\n}","variables":{}}';
                        $headers = [
                            'Authorization' => 'Bearer '.$access_token,
                            'Content-Type' => 'application/json'
                            ];
                        $response = $client->request('POST', 'https://api.swan.io/sandbox-partner/graphql', [
                            'body' => $body,
                            'headers' => $headers
                        ]);
                        $res_body = json_decode($response->getBody());
    
                        $accountlist = $res_body->data->accounts->edges ?? '';
                    } catch (\Throwable $th) {
                        return redirect()->back()->with('unsuccess',$th->getMessage());
                    }
                    $accountid = '';
                    // dd($accountlist);
                    if (count($accountlist) > 0) {
                        foreach ($accountlist as $key => $value) {
                            if ($value->node->IBAN == $customer_bank->iban) {
                                $accountid = $value->node->id;
                                break;
                            }
                        }
                    }
                    else {
                        return redirect()->back()->with('unsuccess','This bank account does not exist in SWAN.');
                    }
                    if ($accountid == '') {
                        return redirect()->back()->with('unsuccess','This bank account does not exist in SWAN.');
                    }
                    try {
                        $body = '{"query":"mutation initiateCreditTransfers($input: InitiateCreditTransfersInput!) {\\n  initiateCreditTransfers(input: $input) {\\n    __typename\\n    ... on InitiateCreditTransfersSuccessPayload {\\n      __typename\\n      payment {\\n        id\\n        statusInfo {\\n          ... on PaymentConsentPending {\\n            __typename\\n            status\\n            consent {\\n              id\\n              consentUrl\\n              redirectUrl\\n            }\\n          }\\n          ... on PaymentInitiated {\\n            __typename\\n            status\\n          }\\n          ... on PaymentRejected {\\n            __typename\\n            reason\\n            status\\n          }\\n        }\\n      }\\n    }\\n    ... on AccountNotFoundRejection {\\n      __typename\\n      message\\n    }\\n    ... on ForbiddenRejection {\\n      __typename\\n      message\\n    }\\n  }\\n}\\n","variables":{"input":{"accountId":"'.$accountid.'","consentRedirectUrl":"'.route('admin.dashboard').'","creditTransfers":{"sepaBeneficiary":{"iban":"'.$request->account_iban.'","name":"'.$paybeneficiary->name.'","isMyOwnIban":false,"save":false},"amount":{"currency":"'.$currency->code.'","value":'.$request->amount.'},"reference":"'.$request->des.'"}}}}';
                        $headers = [
                            'Authorization' => 'Bearer '.$access_token,
                            'Content-Type' => 'application/json'
                            ];
                        $response = $client->request('POST', 'https://api.swan.io/sandbox-partner/graphql', [
                            'body' => $body,
                            'headers' => $headers
                        ]);
                        $res_body = json_decode($response->getBody());
                        $transaction_id = $res_body->data->initiateCreditTransfers->payment->id;
                        $confirm_url = $res_body->data->initiateCreditTransfers->payment->statusInfo->consent->consentUrl;
                        $msg = __('Status Updated Successfully. Please following url to confirm payment. ').$confirm_url;
                    } catch (\Throwable $th) {
                        return redirect()->back()->with('unsuccess',$th->getMessage());
                    }
    
                }
            }
            else {
                $transaction_id = str_rand();
            }
  
    

            $data = new BalanceTransfer();

            // $txnid = Str::random(4).time();
            if ($file = $request->file('document'))
            {
                $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
                $file->move('assets/doc',$name);
                $data->document = $name;
            }

            $data->user_id = auth()->user()->id;
            $data->transaction_no = $transaction_id;
            $data->currency_id = $request->currency_id;
            $data->subbank = $request->subbank;
            $data->iban = $request->account_iban;
            $data->swift_bic = $request->swift_bic;
            $data->beneficiary_id = $request->beneficiary_id;
            $data->type = 'other';
            $data->cost = $transaction_global_cost*$rate;
            $data->payment_type = $request->payment_type;
            $data->amount = $request->amount + $transaction_global_cost*$rate;
            $data->final_amount = $request->amount;
            $data->description = $request->des;
            $data->status = 0;
            $data->save();

            // $trans = new Transaction();
            // $trans->trnx = $txnid;
            // $trans->user_id     = $user->id;
            // $trans->user_type   = 1;
            // $trans->currency_id = Currency::whereIsDefault(1)->first()->id;
            // $trans->amount      = $finalAmount;
            // $trans->charge      = $cost;
            // $trans->type        = '-';
            // $trans->remark      = 'Send_Money';
            // $trans->data        = '{"sender":"'.$user->name.'", "receiver":"Other Bank"}';
            // $trans->details     = trans('Send Money');

            // // $trans->email = $user->email;
            // // $trans->amount = $finalAmount;
            // // $trans->type = "Send Money";
            // // $trans->profit = "minus";
            // // $trans->txnid = $txnid;
            // // $trans->user_id = $user->id;
            // $trans->save();

            // $user->decrement('balance',$finalAmount);
            // $currency = defaultCurr();
            // user_wallet_decrement(auth()->id(),$currency->id,$finalAmount);

            return redirect(route('user.beneficiaries.index'))->with('message','Money Send successfully.');

        }

    }
}
