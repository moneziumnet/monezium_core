<?php

namespace App\Http\Controllers\API;


use App\Models\MerchantShop;
use App\Models\MerchantCheckout;
use App\Models\MerchantWallet;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\Generalsetting;
use App\Models\User;
use App\Models\BankAccount;
use App\Models\DepositBank;
use App\Models\Wallet;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon as Carbontime;
use GuzzleHttp\Client;

class MerchantCheckoutController extends Controller
{

    public function index(){
        try {
            $data['checkouts'] = MerchantCheckout::where('user_id',auth()->id())->get();
            $data['shops'] = MerchantShop::where('merchant_id', auth()->id())->whereStatus(1)->get();
            if (isEnabledUserModule('Crypto'))
                $data['currencylist'] = Currency::whereStatus(1)->get();
            else
            $data['currencylist'] = Currency::whereStatus(1)->where('type', 1)->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }


    public function store(Request $request){
        try {
            $data = new MerchantCheckout();
            $input = $request->all();
            $input['ref_id'] = 'MC-'.Str::random(6);
            $data->fill($input)->save();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Merchant Checkout has been created successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }



    public function transaction(Request $request) {
        try {
            $data = MerchantCheckout::whereId($request->check_id)->first();


            if(!$data) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This Checkout does not exist.']);
            }
            if($data->status == 0) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This Checkout\'s status is deactive']);
            }

            if($request->payment == 'gateway'){

                $user = User::findOrFail($data->user_id);

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $data->user_id;
                $trans->user_type   = 1;
                $trans->currency_id = $data->currency_id;
                $trans->amount      = $request->amount;
                $trans->charge      = 0;
                $trans->type        = '+';
                $trans->remark      = 'merchant_checkout';
                $trans->details     = trans('Merchant Checkout');
                $trans->data        = '{"sender":"'.$request->user_name.'","status":"Pending","shop":"'.$data->shop->name.'", "receiver":"'.($user->company_name ?? $user->name).'"}';
                $trans->save();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You have done successfully']);
            }
            elseif($request->payment == 'wallet'){
                if(!auth()->user()) {
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You have to login for this payment.']);
                }

                $wallet = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('currency_id',$data->currency_id)->where('wallet_type', 1)->first();

                $gs = Generalsetting::first();
                if(!$wallet){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You have no '.$data->currency->code.' current wallet to pay for this.']);
                }

                if($wallet->balance < $request->amount) {
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Insufficient balance to your wallet']);
                }

                $wallet->balance -= $request->amount;
                $wallet->update();
                $user = User::findOrFail($data->user_id);

                $trnx              = new Transaction();
                $trnx->trnx        = str_rand();
                $trnx->user_id     = auth()->id();
                $trnx->user_type   = 1;
                $trnx->currency_id = $data->currency_id;
                $trnx->wallet_id   = $wallet->id;
                $trnx->amount      = $request->amount;
                $trnx->charge      = 0;
                $trnx->remark      = 'merchant_checkout';
                $trnx->type        = '-';
                $trnx->details     = trans('Payment to checkout : '). $data->ref_id;
                $trnx->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name ).'","status":"Completed", "receiver":"'.(User::findOrFail($data->user_id)->company_name ?? User::findOrFail($data->user_id)->name).'"}';
                $trnx->save();

                $rcvWallet = MerchantWallet::where('merchant_id', $data->user_id)->where('shop_id', $data->shop_id)->where('currency_id', $data->currency_id)->first();

                $rcvWallet->balance += $request->amount;
                $rcvWallet->update();


                $trans = new Transaction();
                $trans->trnx = $trnx->trnx;
                $trans->user_id     = $data->user_id;
                $trans->user_type   = 1;
                $trans->currency_id = $data->currency_id;
                $trans->amount      = $request->amount;
                $trans->charge      = 0;
                $trans->type        = '+';
                $trans->remark      = 'merchant_checkout';
                $trans->details     = trans('Merchant Checkout');
                $trans->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "status":"Completed","shop":"'.$data->shop->name.'", "receiver":"'.($user->company_name ?? $user->name).'"}';
                $trans->save();

                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You have done successfully.']);
            }
            elseif($request->payment == 'bank_pay'){

                $bankaccount = BankAccount::where('id', $request->bank_account)->first();
                $deposit = new DepositBank();
                $deposit['deposit_number'] = $request->deposit_no;
                $deposit['user_id'] = $data->user_id;
                $deposit['currency_id'] = $data->currency_id;
                $deposit['amount'] = $request->amount;
                $deposit['sub_bank_id'] = $bankaccount->subbank_id;
                $deposit['details'] = $request->description;
                $deposit['status'] = "pending";
                $deposit->save();
                $currency = Currency::where('id',$data->currency_id)->first();

                $subbank = SubInsBank::findOrFail($bankaccount->subbank_id);
                $user = User::findOrFail($bankaccount->user_id);
                mailSend('deposit_request',['amount'=>$deposit->amount, 'curr' => ($currency ? $currency->code : ' '), 'date_time'=>$deposit->created_at ,'type' => 'Bank', 'method'=> $subbank->name ], $user);

                send_notification($data->user_id, 'Bank has been deposited by '.$request->user_name."\n Amount is ".$currency->symbol.$request->amount."\n Transaction ID : ".$request->deposit_no, route('admin.deposits.bank.index'));
                send_whatsapp($data->user_id, 'Bank has been deposited by '.$request->user_name."\n Amount is ".$currency->symbol.$request->amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                send_telegram($data->user_id, 'Bank has been deposited by '.$request->user_name."\n Amount is ".$currency->symbol.$request->amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                send_staff_telegram('Bank has been deposited by '.$request->user_name."\n Amount is ".$currency->symbol.$request->amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('admin.deposits.bank.index'), 'Deposit Bank');

                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You have done successfully (Deposit Bank).']);
            }
            elseif($request->payment == 'crypto') {
                $user = User::findOrFail($data->user_id);

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $data->user_id;
                $trans->user_type   = 1;
                $trans->currency_id = $request->currency_id;
                $trans->amount      = $request->amount;
                $trans->charge      = 0;
                $trans->type        = '+';
                $trans->remark      = 'merchant_checkout';
                $trans->details     = trans('Merchant Checkout');
                $trans->data        = '{"sender":"'.$request->user_name.'","status":"Pending","shop":"'.$data->shop->name.'", "receiver":"'.($user->company_name ?? $user->name).'"}';
                $trans->save();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You have done successfully']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function transactionhistory() {
        try {
            $user = auth()->user();
            $search = request('search');
            $s_time = request('s_time');
            $e_time = request('e_time');
            $s_time = $s_time ? $s_time : '';
            $e_time = $e_time ? $e_time : Carbontime::now()->addDays(1)->format('Y-m-d');
            $transactions = Transaction::where('user_id',auth()->id())
            ->where('remark', 'merchant_checkout')
            ->when($search,function($q) use($search){
                return $q->where('trnx','LIKE',"%{$search}%");
            })
            ->whereBetween('created_at', [$s_time, $e_time])
            ->with('currency')->latest()->paginate(20);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => compact('user','transactions', 'search',  's_time', 'e_time')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function edit($id) {
        try {
            $data['data'] = MerchantCheckout::findOrFail($id);
            $data['shops'] = MerchantShop::where('merchant_id', auth()->id())->get();
            if (isEnabledUserModule('Crypto'))
                $data['currencylist'] = Currency::whereStatus(1)->get();
            else
                $data['currencylist'] = Currency::whereStatus(1)->where('type', 1)->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function update(Request $request, $id) {
        try {
            $data = MerchantCheckout::findOrFail($id);

            $input = $request->all();
            $data->fill($input)->update();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Merchant Checkout has been updated successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

    public function status($id) {
        try {
            $data = MerchantCheckout::findOrFail($id);
            if($data->status == 1) {
                $data->status = 0;
            }
            else {
                $data->status = 1;
            }
            $data->update();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Merchant Checkout status has been changed successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function transaction_status($id, $status) {
        try {
            $data = Transaction::findOrFail($id);
            $tran_status = json_decode($data->data,true);

            if($tran_status['status'] == 'Completed') {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Merchant Checkout transaction status already is completed']);
            }
            elseif($tran_status['status'] == 'Rejected') {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Merchant Checkout transaction status already is rejected']);
            }
            else {
                $tran_status['status'] = $status;
                if ($status == 'Completed') {
                    $shop = MerchantShop::where('name',$tran_status['shop'])->first();
                    $cryptowallet = MerchantWallet::where('merchant_id', $data->user_id)->where('shop_id', $shop->id)->where('currency_id', $data->currency_id)->first();
                    $cryptowallet->balance += $data->amount;
                    $cryptowallet->save();
                }
            }
            $data->data = json_encode($tran_status);
            $data->update();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Merchant Checkout transaction status has been changed successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function delete($id) {
        try {
            $data = MerchantCheckout::findOrFail($id);
            $data->delete();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Merchant Checkout status has been deleted successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

    public function send_email(Request $request)
    {
        try {
            $to = $request->email;
            $subject = "Checkout";
            $msg = "Please check <a href='".$request->link."'>this link</a>";
            $headers = "From: ".auth()->user()->name."<".auth()->user()->email.">";
            $headers .= "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            sendMail($to,$subject,$msg,$headers);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Email is sent successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }
}

