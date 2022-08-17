<?php

namespace App\Http\Controllers\Admin;

use Datatables;

use App\Models\Wallet;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Exports\AdminExportTransaction;


class SystemAccountController extends Controller
{
    public function __construct()
        {
            $this->middleware('auth:admin');
        }

    public function systemAccounts()
        {
            $wallets = Wallet::where('user_id',0)->where('wallet_type', 9)->with('currency')->get();
            $data['wallets'] = $wallets;
            return view('admin.systemwallet',$data);
        }

    public function create($currency_id)
        {
            {
                $wallet = Wallet::where('user_id', 0)->where('wallet_type', 9)->where('currency_id', $currency_id)->first();
                $gs = Generalsetting::first();
                if(!$wallet)
                {
                  $user_wallet = new Wallet();
                  $user_wallet->user_id = 0;
                  $user_wallet->user_type = 1;
                  $user_wallet->currency_id = $currency_id;
                  $user_wallet->balance = 0;
                  $user_wallet->wallet_type = 9;
                  $user_wallet->wallet_no =$gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999);
                  $user_wallet->created_at = date('Y-m-d H:i:s');
                  $user_wallet->updated_at = date('Y-m-d H:i:s');
                  $user_wallet->save();

                  $msg = __('Account New Wallet Updated Successfully.');
                  return response()->json($msg);
                }
                else {
                    return response()->json(array('errors' => [0 =>'This wallet has already been created.']));
                }

              }
        }

}
