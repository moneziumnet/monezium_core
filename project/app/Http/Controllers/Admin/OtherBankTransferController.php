<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BalanceTransfer;
use App\Models\Beneficiary;
use App\Models\Currency;
use App\Models\BankAccount;
use App\Models\BankGateway;
use App\Models\SubInsBank;
use App\Models\Transaction;
use App\Models\BankPoolAccount;
use GuzzleHttp\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Datatables;

class OtherBankTransferController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:admin');
  }

  public function datatables()
  {
    $datas = BalanceTransfer::whereType('other')->orderBy('id', 'desc');

    return Datatables::of($datas)

      ->editColumn('user_id', function (BalanceTransfer $data) {
        $data = User::whereId($data->user_id)->first();
        if ($data) {
          return '<div>
            <span>' . $data->name . '</span>
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
        return $curr->symbol . $data->amount;
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
            <span>' . $data->name . '</span>
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
        return $curr->symbol . $data->amount;
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
    $bankaccount = BankAccount::whereUserId($data->user_id)->where('subbank_id',$data->subbank)->where('currency_id', $data->currency_id)->first();

    return view('admin.otherbanktransfer.details', compact('data', 'banefeciary', 'bankaccount'));
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
    if ($id2 == 2) {
      if ($user) {
        // $user->increment('balance', $data->final_amount);
        $currency_id = Currency::whereIsDefault(1)->first()->id;
        // user_wallet_increment($user->id, $currency_id, $data->final_amount);
      }
    }
    if ($id2 == 1) {
        $currency = Currency::where('id',$data->currency_id)->first();
        $customer_bank = BankAccount::whereUserId($user->id)->where('subbank_id',$data->subbank)->where('currency_id', $data->currency_id)->first();
        $bankgateway = BankGateway::where('subbank_id', $data->subbank)->first();
        $master_account = BankPoolAccount::where('bank_id', $data->subbank)->where('currency_id', $data->currency_id)->first();

        $subbank = SubInsBank::where('id', $data->subbank)->first();
        $client = New Client();
        if($bankgateway->keyword == 'openpayd') {

            try {
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
            } catch (\Throwable $th) {
              return response()->json(array('errors' => [ 0 => $th->getMessage() ]));
            }


            try {
                $response = $client->request('GET', 'https://sandbox.openpayd.com/api/accounts?iban='.$customer_bank->iban, [
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
                $response = $client->request('GET', 'https://sandbox.openpayd.com/api/accounts?iban='.$master_account->iban, [
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
                if ($master_amount < $data->amount) {
                    return response()->json(array('errors' => [ 0 => __('Your balance is Insufficient') ]));
                }
            } catch (\Throwable $th) {
                 return response()->json(array('errors' => [ 0 => $th->getMessage() ]));
            }

            try {
                $response = $client->request('POST', 'https://sandbox.openpayd.com/api/transactions/sweepPayout', [
                    'body' =>
                        '{"beneficiary":
                            {"bankAccountCountry":"'.substr($data->iban, 0,2).'",
                            "customerType":"RETAIL",
                            "firstName":"'.$data->beneficiary->name.'",
                            "lastName":"'.$data->beneficiary->name.'",
                            "iban":"'.$data->iban.'",
                            "bic":"'.$data->swift_bic.'"
                            },
                        "amount":
                            {"value":"'.$data->amount.'",
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
        else {

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
                        "amount": "'.$data->amount.'"
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


            user_wallet_decrement($user->id, $data->currency_id, $data->final_amount);
            user_wallet_increment(0, $data->currency_id, $data->cost, 9);

            $trans = new Transaction();
            $trans->trnx = Str::random(4).time();
            $trans->user_id     = $data->user_id;
            $trans->user_type   = 1;
            $trans->currency_id = $data->currency_id;
            $trans->amount      = $data->final_amount;
            $trans->charge      = $data->cost;
            $trans->type        = '-';
            $trans->remark      = 'External_Payment';
            $trans->data        = '{"sender":"'.$user->name.'", "receiver":"'.$data->beneficiary->name.'", "transaction_id":"'.$transaction_id.'"}';
            $trans->details     = trans('Send Money');
            $trans->save();
    }

    $data->status = $id2;
    $data->update();

    $msg = __('Status Updated Successfully.');
    return response()->json($msg);
  }
}
