<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BalanceTransfer;
use App\Models\WebhookRequest;
use App\Models\Beneficiary;
use App\Models\Currency;
use App\Models\BankAccount;
use App\Models\BankGateway;
use App\Models\SubInsBank;
use App\Models\Transaction;
use App\Models\BankPoolAccount;
use App\Models\Generalsetting;
use GuzzleHttp\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Datatables;
use DateTime;
use GuzzleHttp\Exception\RequestException;

class OtherBankTransferController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:admin');
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

  public function datatables()
  {
    $datas = BalanceTransfer::whereType('other')->orderBy('id', 'desc');

    return Datatables::of($datas)
      ->setRowAttr([
        'style' => function(BalanceTransfer $data) {
            $transaction_id = $data->transaction_no;
            $webhook_request = WebhookRequest::where('transaction_id', $transaction_id )->first();
            if(($data->status == '0' || $data->status == '3') && (!$webhook_request || $webhook_request->status == "processing")) {
                return "background-color: #ffcaca;";
            } else {
                return "background-color: #ffffff;";
            }
        },
      ])

      ->editColumn('date', function(BalanceTransfer $data) {
        $date = date('d-M-Y',strtotime($data->created_at));
        return $date;
      })

      ->editColumn('user_id', function (BalanceTransfer $data) {
        $data = User::whereId($data->user_id)->first();
        if ($data) {
          return '<div>
            <span>' . ($data->company_name ?? $data->name) . '</span>
          </div>';
        } else {
          return $data = '';
        }
      })

      ->editColumn('beneficiary_id', function (BalanceTransfer $data) {
        $data = Beneficiary::whereId($data->beneficiary_id)->first();

        if ($data) {
          return '<div>
            <span>' . $data->name . '</span>
          </div>';
        } else {
          return $data = '';
        }
      })

      ->editColumn('amount', function (BalanceTransfer $data) {
        $curr = Currency::where('id', $data->currency_id)->first();
        return $curr->symbol . $data->final_amount;
      })

      ->editColumn('cost', function (BalanceTransfer $data) {
        $curr = Currency::where('id', $data->currency_id)->first();
        return $curr->symbol . $data->cost;
      })

      ->editColumn('status', function (BalanceTransfer $data) {
        if ($data->status == 1) {
          $status  = __('Completed');
        } elseif ($data->status == 2) {
          $status  = __('Rejected');
        } else {
          $status  = __('Pending');
        }

        if ($data->status == 1) {
          $status_sign  = 'success';
        } elseif ($data->status == 2) {
          $status_sign  = 'danger';
        } else {
          $status_sign = 'warning';
        }
        return '<span class="badge badge-'.$status_sign.'">'.$status.'</span>';
      })

      ->addColumn('action', function (BalanceTransfer $data) {
        return '<div class="btn-group mb-1">
          <button type="button" class="btn btn-primary btn-sm" onclick="getDetails(event)" id="'.$data->id.'">Details</button>
        </div>';
      })

      ->rawColumns(['user_id', 'beneficiary_id', 'amount', 'cost', 'status', 'action'])
      ->toJson();
  }

  public function subdatatables(Request $request)
  {

    $datas = BalanceTransfer::whereType('other')->where('user_id', $request->id)->orderBy('id', 'desc');

    return Datatables::of($datas)

      ->editColumn('user_id', function (BalanceTransfer $data) {
        $data = User::whereId($data->user_id)->first();
        if ($data) {
          return '<div>
            <span>' . ($data->company_name ?? $data->name) . '</span>
          </div>';
        } else {
          return $data = '';
        }
      })

      ->editColumn('beneficiary_id', function (BalanceTransfer $data) {
        $data = Beneficiary::whereId($data->beneficiary_id)->first();

        if ($data) {
          return '<div>
            <span>' . $data->name . '</span>
          </div>';
        } else {
          return $data = '';
        }
      })

      ->editColumn('amount', function (BalanceTransfer $data) {
        $curr = Currency::where('id', $data->currency_id)->first();
        return $curr->symbol . $data->final_amount;
      })

      ->editColumn('cost', function (BalanceTransfer $data) {
        $curr = Currency::where('id', $data->currency_id)->first();
        return $curr->symbol . $data->cost;
      })

      ->editColumn('status', function (BalanceTransfer $data) {
        if ($data->status == 1) {
          $status  = __('Completed');
        } elseif ($data->status == 2) {
          $status  = __('Rejected');
        } else {
          $status  = __('Pending');
        }

        if ($data->status == 1) {
          $status_sign  = 'success';
        } elseif ($data->status == 2) {
          $status_sign  = 'danger';
        } else {
          $status_sign = 'warning';
        }
        return '<span class="badge badge-'.$status_sign.'">'.$status.'</span>';
      })

      ->addColumn('action', function (BalanceTransfer $data) {
        return '<div class="btn-group mb-1">
          <button type="button" class="btn btn-primary btn-sm" onclick="getDetails(event)" id="'.$data->id.'">Details</button>
        </div>';
      })

      ->rawColumns(['user_id', 'beneficiary_id', 'amount', 'cost', 'status', 'action'])
      ->toJson();
  }

  public function index()
  {
    return view('admin.otherbanktransfer.index');
  }

  public function show($id)
  {
    $data = BalanceTransfer::whereId($id)->first();
    $banefeciary = Beneficiary::whereId($data->beneficiary_id)->first();
    $bankaccount = BankAccount::whereUserId($data->user_id)->where('subbank_id',$data->subbank)->where('currency_id', $data->currency_id)->first();

    return view('admin.otherbanktransfer.show', compact('data', 'banefeciary', 'bankaccount'));
  }

  public function details($id)
  {
    $data = BalanceTransfer::whereId($id)->first();
    $banefeciary = Beneficiary::whereId($data->beneficiary_id)->first();
    $subbank = SubInsBank::find($data->subbank);
    $user = User::findOrFail($data->user_id);
    $webhook_request = WebhookRequest::where('transaction_id', $data->transaction_no )->first();
    $nogateway = $subbank->hasGateway() ? false : true;
    if($subbank->hasGateway()){
        $bankaccount = BankAccount::whereUserId($data->user_id)->where('subbank_id', $data->subbank)->where('currency_id', $data->currency_id)->first();
    } else {
        $bankaccount = BankPoolAccount::where('bank_id', $data->subbank)->where('currency_id', $data->currency_id)->first();
    }

    return view('admin.otherbanktransfer.details', compact('data', 'banefeciary', 'bankaccount', 'user', 'webhook_request', 'nogateway'));
  }

  public function status($id1, $id2)
  {
    $data = BalanceTransfer::findOrFail($id1);
    if ($data->status == 1) {
      return response()->json(array('errors' => [ 0 =>  __('Status already completed.') ]));
    }
    if ($data->status == 2) {
      return response()->json(array('errors' => [ 0 =>  __('Status already rejected.') ]));
    }
    $user = User::whereId($data->user_id)->first();
    if ($id2 == 3) {
        $currency = Currency::where('id',$data->currency_id)->first();
        $customer_bank = BankAccount::whereUserId($user->id)->where('subbank_id',$data->subbank)->where('currency_id', $data->currency_id)->first();
        $bankgateway = BankGateway::where('subbank_id', $data->subbank)->first();
        $master_account = BankPoolAccount::where('bank_id', $data->subbank)->where('currency_id', $data->currency_id)->first();

        $subbank = SubInsBank::where('id', $data->subbank)->first();
        $client = New Client();
        $msg = __('Status Updated Successfully.');
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
                return response()->json(array('errors' => [ 0 => $th->getMessage() ]));
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
                return response()->json(array('errors' => [ 0 => $th->getMessage() ]));
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
                    if ($master_amount < $data->final_amount) {
                        return response()->json(array('errors' => [ 0 => __('Your balance is Insufficient') ]));
                    }
                } catch (\Throwable $th) {
                    return response()->json(array('errors' => [ 0 => $th->getMessage() ]));
                }

                try {
                    $customer_name = $data->beneficiary->type == 'RETAIL' ? '"firstName":"'.explode(" ",$data->beneficiary->name, 2)[0].'","lastName":"'.explode(" ",$data->beneficiary->name, 2)[1].'",' : '"companyName":"'.$data->beneficiary->name.'",';
                    if (substr($data->iban, 0,2) == 'GB' && $currency->code == 'GBP') {
                        $gb_beneficiary = '"iban":"'.$data->iban.'",
                        "bic":"'.$data->swift_bic.'",'.'"routingCodes": {
                            "SORT_CODE": "'.substr($data->iban, -14, 6).'"
                       },
                       "accountNumber": "'.substr($data->iban, -8, 8).'"';
                    } else {
                        $gb_beneficiary = '"iban":"'.$data->iban.'",
                                "bic":"'.$data->swift_bic.'"';
                    }
                    $response = $client->request('POST', 'https://secure-mt.openpayd.com/api/transactions/sweepPayout', [
                        'body' =>
                            '{"beneficiary":
                                {"bankAccountCountry":"'.substr($data->iban, 0,2).'",
                                "customerType":"'.$data->beneficiary->type.'",
                                '.$customer_name.
                                $gb_beneficiary.'
                                },
                            "amount":
                                {"value":"'.$data->final_amount.'",
                                "currency":"'.$currency->code.'"
                                },
                            "linkedAccountHolderId":"'.$user->holder_id.'",
                            "accountId":"'.$account_id.'",
                            "sweepSourceAccountId":"'.$master_account_id.'",
                            "paymentType":"'.$data->payment_type.'",
                            "reference":"'.$data->description.'"
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
                return response()->json(array('errors' => [ 0 => $th->getMessage() ]));
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
                    if ($amount < $data->final_amount) {
                        return redirect()->back()->with(array('warning' => 'Insufficient Balance.'));
                    }
                } catch (\Throwable $th) {
                return response()->json(array('errors' => [ 0 => $th->getMessage() ]));
                }
                try {

                    $response = $client->request('POST','https://play.railsbank.com/v1/customer/beneficiaries', [
                        'body' => '{
                            "holder_id": "'.$enduser.'",
                            "asset_class": "currency",
                            "asset_type": "eur",
                            "iban": "'.$data->iban.'",
                            "bic_swift": "'.$data->swift_bic.'",
                            "person": {
                            "name": "'.$data->beneficiary->name.'",
                            "email": "'.$data->beneficiary->email.'",
                            "address": { "address_iso_country": "'.substr($data->iban, 0,2).'" }
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
                return response()->json(array('errors' => [ 0 => $th->getMessage() ]));
                }
                try {
                    $response = $client->request('POST', 'https://play.railsbank.com/v1/customer/transactions', [
                        'body' => '{
                            "ledger_from_id": "'.$ledger.'",
                            "beneficiary_id": "'.$beneficiary.'",
                            "payment_type": "payment-type-EU-SEPA-Step2",
                            "amount": "'.$data->final_amount.'"
                        }',
                        'headers' => [
                        'Accept'=> 'application/json',
                        'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
                        'Content-Type' => 'application/json',
                        ],
                    ]);
                    $transaction_id = json_decode($response->getBody())->transaction_id;
                } catch (\Throwable $th) {
                return response()->json(array('errors' => [ 0 => $th->getMessage() ]));
                }
            }
            else if($bankgateway->keyword == 'clearjunction') {
                $clientorder = rand(1000000, 9999999);
                $payer_type = $user->company_name ? "corporate" : "individual";
                $payer_name = $user->company_name ?  '"name":"'.$user->company_name.'"' : '"firstName":"'.explode(" ",$user->name, 2)[0].'","lastName":"'.explode(" ",$user->name, 2)[1].'"';
                $type =   $data->beneficiary->type == 'RETAIL' ? "individual" : "corporate";
                $payee_name = $data->beneficiary->type == 'RETAIL' ? '"firstName":"'.explode(" ",$data->beneficiary->name, 2)[0].'","lastName":"'.explode(" ",$data->beneficiary->name, 2)[1].'"' : '"name":"'.$data->beneficiary->name.'"';
                $body = '{
                    "clientOrder": "'.$clientorder.'",
                    "currency": "'.$currency->code.'",
                    "amount": '.$data->final_amount.',
                    "description": "'.$data->description.'",
                    "postbackUrl": "'.url('/cj-payout').'",
                    "payee": {
                      "'.$type.'": {
                        '.$payee_name.'
                      }
                    },
                    "payer": {
                      "'.$payer_type.'": {
                        '.$payer_name.'
                      }
                    },
                    "payeeRequisite": {
                      "iban": "'.$data->iban.'",
                      "bankSwiftCode": "'.$data->swift_bic.'"
                    },
                    "payerRequisite": {
                      "iban": "'.$customer_bank->iban.'",
                      "bankSwiftCode": "'.$customer_bank->swift.'"
                    }
                  }';
                  $param = $this->getToken($body, $data->subbank);
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
                }  catch (RequestException $th) {
                  return response()->json(array('errors' =>  [ 0 => json_decode($th->getResponse()->getBody())->errors[0]->message]));
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
                    return response()->json(array('errors' => [ 0 => $th->getMessage() ]));
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
                    return response()->json(array('errors' => [ 0 => $th->getMessage() ]));
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
                    return response()->json(array('errors' => [ 0 => 'This bank account does not exist in SWAN.' ]));
                }
                if ($accountid == '') {
                    return response()->json(array('errors' => [ 0 => 'This bank account does not exist in SWAN.' ]));
                }
                try {
                    $body = '{"query":"mutation initiateCreditTransfers($input: InitiateCreditTransfersInput!) {\\n  initiateCreditTransfers(input: $input) {\\n    __typename\\n    ... on InitiateCreditTransfersSuccessPayload {\\n      __typename\\n      payment {\\n        id\\n        statusInfo {\\n          ... on PaymentConsentPending {\\n            __typename\\n            status\\n            consent {\\n              id\\n              consentUrl\\n              redirectUrl\\n            }\\n          }\\n          ... on PaymentInitiated {\\n            __typename\\n            status\\n          }\\n          ... on PaymentRejected {\\n            __typename\\n            reason\\n            status\\n          }\\n        }\\n      }\\n    }\\n    ... on AccountNotFoundRejection {\\n      __typename\\n      message\\n    }\\n    ... on ForbiddenRejection {\\n      __typename\\n      message\\n    }\\n  }\\n}\\n","variables":{"input":{"accountId":"'.$accountid.'","consentRedirectUrl":"'.route('admin.dashboard').'","creditTransfers":{"sepaBeneficiary":{"iban":"'.$data->iban.'","name":"'.$data->beneficiary->name.'","isMyOwnIban":false,"save":false},"amount":{"currency":"'.$currency->code.'","value":'.$data->final_amount.'},"reference":"'.$data->description.'"}}}}';
                    $headers = [
                        'Authorization' => 'Bearer '.$access_token,
                        'Content-Type' => 'application/json'
                        ];
                    $response = $client->request('POST', 'https://api.swan.io/sandbox-partner/graphql', [
                        'body' => $body,
                        'headers' => $headers
                    ]);
                    $res_body = json_decode($response->getBody());
                    if ($res_body->data->initiateCreditTransfers->message) {
                      return response()->json(array('errors' => [ 0 => $res_body->data->initiateCreditTransfers->message.' This Bank gateway is not on live.' ]));
                    }
                    $transaction_id = $res_body->data->initiateCreditTransfers->payment->id;
                    $confirm_url = $res_body->data->initiateCreditTransfers->payment->statusInfo->consent->consentUrl;
                    $msg = __('Status Updated Successfully. Please following url to confirm payment. ').$confirm_url;
                } catch (\Throwable $th) {
                    return response()->json(array('errors' => [ 0 => $th->getMessage() ]));
                }

            }
        }
        else {
            $transaction_id = str_rand();
        }

        $data->transaction_no = $transaction_id;
        $data->status = $id2;
        $data->update();

    }

    if ($id2 == 1) {
      // user_wallet_decrement($user->id, $data->currency_id, $data->amount);
      // user_wallet_increment(0, $data->currency_id, $data->cost, 9);

    //   user_wallet_decrement($user->id, $data->currency_id, $data->amount);
    //   user_wallet_increment(0, $data->currency_id, $data->cost, 9);
      $currency = Currency::findOrFail($data->currency_id);
      $rate = getRate($currency);
      $transaction_custom_cost = 0;
      if($user->referral_id != 0) {
        $transaction_custom_fee = check_custom_transaction_fee($data->final_amount, $user, 'withdraw');
        if($transaction_custom_fee) {
            $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($data->final_amount/($rate*100)) * $transaction_custom_fee->data->percent_charge;
        }
        $remark='withdraw_supervisor_fee';
        if (check_user_type_by_id(4, $user->referral_id)) {
            user_wallet_decrement($user->id, $data->currency_id,$transaction_custom_cost*$rate,1);
            user_wallet_increment($user->referral_id, $data->currency_id, $transaction_custom_cost*$rate, 6);
            $trans_wallet = get_wallet($user->referral_id, $data->currency_id, 6);
        }
        elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
            $remark='withdraw_manager_fee';
            user_wallet_decrement($user->id, $data->currency_id,$transaction_custom_cost*$rate,1);
            user_wallet_increment($user->referral_id, $data->currency_id, $transaction_custom_cost*$rate, 10);
            $trans_wallet = get_wallet($user->referral_id, $request->currency_id, 10);
        }

        $trnx = str_rand();
        $gs = Generalsetting::findOrFail(1);
        $trans = new Transaction();
        $trans->trnx = $trnx;
        $trans->user_id     = $user->referral_id;
        $trans->user_type   = 1;
        $trans->currency_id = $data->currency_id;

        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

        $trans->amount      = $transaction_custom_cost*$rate;
        $trans->charge      = 0;
        $trans->type        = '+';
        $trans->remark      = $remark;
        $trans->details     = trans('Withdraw money');
        $trans->data        = '{"sender":"'.$gs->disqus.'", "receiver":"'.(User::findOrFail($user->referral_id)->company_name ?? User::findOrFail($user->referral_id)->name).'"}';
        $trans->save();
    }
      $trans_wallet = get_wallet($user->id, $data->currency_id);


      $trans = new Transaction();
      $trans->trnx = $data->transaction_no;
      $trans->user_id     = $data->user_id;
      $trans->user_type   = 1;
      $trans->currency_id = $data->currency_id;
      $trans->amount      = $data->amount;

      $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

      $trans->charge      = $data->cost + $transaction_custom_cost*$rate;
      $trans->type        = '-';
      $trans->remark      = 'withdraw';
      $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$data->beneficiary->name.'", "transaction_id":"'.$data->transaction_no.'", "description":"'.$data->description.'"}';
      $trans->details     = trans('Send Money');
      $trans->save();

      $data->status = $id2;
      $data->update();

      $currency =  Currency::findOrFail($data->currency_id);
      $subbank = SubInsBank::findOrFail($data->subbank);
      mailSend('accept_withdraw',['amount'=>amount($data->final_amount,1,2), 'trnx'=> $trans->trnx,'curr' => $currency->code,'method'=>$subbank->name,'charge'=> amount($data->cost,1,2),'date_time'=> dateFormat($data->updated_at)], $user);
      send_notification($user->id, 'Bank transfer has been completed'.".\n Amount is ".$currency->symbol.$data->final_amount."\n Payment Gateway:".$subbank->name."\n Charge:".$currency->symbol.amount($data->cost,1,2)."\n Transaction ID:".$data->transaction_no."\n Status:Complete", route('admin-user-banks', $user->id));
      send_staff_telegram('Bank transfer has been completed by '.($user->company_name ?? $user->name).".\n Amount is ".$currency->symbol.$data->final_amount."\n Payment Gateway:".$subbank->name."\n Charge:".$currency->symbol.amount($data->cost,1,2)."\n Transaction ID:".$data->transaction_no."\n Status:Complete"."\n Please check.\n".route('admin-user-banks', $user->id), 'Bank Transfer');
      $msg = __('Status Updated Successfully.');
    }

    if ($id2 == 2) {
      $data->status = $id2;
      $data->update();
      user_wallet_increment($user->id, $data->currency_id, $data->amount);
      user_wallet_decrement(0, $data->currency_id, $data->cost, 9);
      $currency =  Currency::findOrFail($data->currency_id);
      $subbank = SubInsBank::findOrFail($data->subbank);
      mailSend('reject_withdraw',['amount'=> amount($data->final_amount,1,2), 'trnx'=> $data->transaction_no,'curr' => $currency->code,'method'=>$subbank->name,'reason'=>'Admin reject your request.','date_time'=> dateFormat($data->updated_at)],$user);

      send_notification($user->id, 'Bank transfer has been rejected'.".\n Amount is ".$currency->symbol.$data->final_amount."\n Payment Gateway:".$subbank->name."\n Charge:".$currency->symbol.amount($data->cost,1,2)."\n Transaction ID:".$data->transaction_no."\n Status:Rejected", route('admin-user-banks', $user->id));
      send_staff_telegram('Bank transfer has been rejected by '.($user->company_name ?? $user->name).".\n Amount is ".$currency->symbol.$data->final_amount."\n Payment Gateway:".$subbank->name."\n Charge:".$currency->symbol.amount($data->cost,1,2)."\n Transaction ID:".$data->transaction_no."\n Status:Rejected"."\n Please check.\n".route('admin-user-banks', $user->id), 'Bank Transfer');

      $msg = __('Status Updated Successfully.');
    }

    return response()->json($msg);
  }
}
