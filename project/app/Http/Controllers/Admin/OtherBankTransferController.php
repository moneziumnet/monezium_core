<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BalanceTransfer;
use App\Models\Beneficiary;
use App\Models\Currency;
use App\Models\OtherBank;
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

        return '<div class="btn-group mb-1">
                                <button type="button" class="btn btn-' . $status_sign . ' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                  ' . $status . '
                                </button>
                                <div class="dropdown-menu" x-placement="bottom-start">
                                  <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="' . route('admin.other.banks.transfer.status', ['id1' => $data->id, 'status' => 1]) . '">' . __("completed") . '</a>
                                  <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="' . route('admin.other.banks.transfer.status', ['id1' => $data->id, 'status' => 2]) . '">' . __("rejected") . '</a>
                                </div>
                              </div>';
      })

      ->addColumn('action', function (BalanceTransfer $data) {

        return '<div class="btn-group mb-1">
                                  <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    ' . 'Actions' . '
                                  </button>
                                  <div class="dropdown-menu" x-placement="bottom-start">
                                    <a href="' . route('admin.other.banks.transfer.show', $data->id) . '"  class="dropdown-item">' . __("Details") . '</a>
                                  </div>
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

        return '<div class="btn-group mb-1">
                                <button type="button" class="btn btn-' . $status_sign . ' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                  ' . $status . '
                                </button>
                                <div class="dropdown-menu" x-placement="bottom-start">
                                  <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="' . route('admin.other.banks.transfer.status', ['id1' => $data->id, 'status' => 1]) . '">' . __("completed") . '</a>
                                  <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="' . route('admin.other.banks.transfer.status', ['id1' => $data->id, 'status' => 2]) . '">' . __("rejected") . '</a>
                                </div>
                              </div>';
      })

      ->addColumn('action', function (BalanceTransfer $data) {

        return '<div class="btn-group mb-1">
                                  <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    ' . 'Actions' . '
                                  </button>
                                  <div class="dropdown-menu" x-placement="bottom-start">
                                    <a href="' . route('admin.other.banks.transfer.show', $data->id) . '"  class="dropdown-item">' . __("Details") . '</a>
                                  </div>
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

  public function status($id1, $id2)
  {
    $data = BalanceTransfer::findOrFail($id1);
    if ($data->status == 1) {
      $msg = __('Status already Completed.');
      return response()->json($msg);
    }
    if ($data->status == 2) {
        $msg = __('Status already Rejected.');
        return response()->json($msg);
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
             return response()->json($th->getMessage());
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
             return response()->json($th->getMessage());
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
                return response()->json(('Your balance is Insufficient '));
            }
        } catch (\Throwable $th) {
             return response()->json($th->getMessage());
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
             return response()->json($th->getMessage());
        }



            $trans = new Transaction();
            $trans->trnx = Str::random(4).time();
            $trans->user_id     = $data->user_id;
            $trans->user_type   = 1;
            $trans->currency_id = $data->currency_id;
            $trans->amount      = $data->final_amount;
            $trans->charge      = $data->cost;
            $trans->type        = '-';
            $trans->remark      = 'Send_Money';
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
