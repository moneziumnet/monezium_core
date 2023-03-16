<?php

namespace App\Http\Controllers\User;

use App\Models\Wallet;
use App\Models\Voucher;
use App\Models\Transaction;
use App\Models\Charge;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;

class VoucherController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function vouchers()
    {

        $data['vouchers'] = Voucher::where('user_id',auth()->id())->orderBy('status','asc')->paginate(20);
        return view('user.voucher.vouchers',$data);


    }

    public function create()
    {

        $data['wallets'] = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('balance', '>', 0)->where('wallet_type',1)->get();
        $data['recentVouchers'] = Voucher::where('user_id',auth()->id())->latest()->take(7)->get();
        return view('user.voucher.voucher_create',$data);
    }

    public function submit(Request $request)
    {
        $request->validate([
            'wallet_id' => 'required|integer',
            'amount' => 'required|numeric|gt:0'
        ]);

        $wallet = Wallet::where('id',$request->wallet_id)->where('user_type',1)->where('user_id',auth()->id())->first();
        if(!$wallet) return back()->with('error','Wallet not found');

        $rate = getRate($wallet->currency);

        $user= auth()->user();
        $global_cost = 0;
        $transaction_global_cost = 0;
        $custom_cost = 0;
        $transaction_custom_cost = 0;

        $finalCharge = $custom_cost+$global_cost+$transaction_global_cost+$transaction_custom_cost;
        $finalAmount = $request->amount + $finalCharge;

        $userBalance = user_wallet_balance(auth()->id(), $wallet->currency_id);
        if($finalAmount > $userBalance) return back()->with('error','Wallet has insufficient balance');

        $voucher = new Voucher();
        $voucher->user_id = auth()->id();
        $voucher->currency_id = $wallet->currency_id;
        $voucher->amount = $request->amount;
        $voucher->code = randNum(10).'-'.randNum(10);
        $voucher->save();

        user_wallet_decrement(auth()->id(),$wallet->currency_id,$finalAmount);

        $trnx              = new Transaction();
        $trnx->trnx        = str_rand();
        $trnx->user_id     = auth()->id();
        $trnx->user_type   = 1;
        $trnx->currency_id = $wallet->currency->id;
        $trnx->amount      = $finalAmount;

        $trans_wallet = get_wallet(auth()->id(),$wallet->currency_id);
        $trnx->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

        $trnx->charge      = $finalCharge;
        $trnx->remark      = 'create_voucher';
        $trnx->type        = '-';
        $trnx->details     = trans('Voucher created');
        $trnx->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"Vocher System"}';
        $trnx->save();

        return redirect(route('user.vouchers'))->with('message','Voucher has been created successfully');

    }

    public function reedemForm()
    {
        $data['recentReedemed'] = Voucher::where('status',1)->where('reedemed_by',auth()->id())->take(7)->get();
        return view('user.voucher.reedem',$data);
    }

    public function reedemSubmit(Request $request)
    {
       $request->validate(['code' => 'required']);

       $voucher = Voucher::where('code',$request->code)->where('status',0)->first();

       if(!$voucher){
           return back()->with('error','Invalid voucher code');
       }

       if( $voucher->user_id == auth()->id()){
          return back()->with('error','Can\'t reedem your own voucher');
       }

       $wallet = Wallet::where('currency_id',$voucher->currency_id)->where('user_id',auth()->id())->where('wallet_type', 1)->first();
       if(!$wallet){
          $gs = Generalsetting::first();
          $wallet = Wallet::create([
              'user_id' => auth()->id(),
              'user_type' => 1,
              'currency_id' => $voucher->currency_id,
              'balance'   => 0,
              'wallet_type' => 1,
              'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
          ]);

          $user = User::findOrFail(auth()->id());

          $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
          if(!$chargefee) {
              $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
          }

          $trans = new Transaction();
          $trans->trnx = str_rand();
          $trans->user_id     = $user->id;
          $trans->user_type   = 1;
          $trans->currency_id = defaultCurr();
          $trans->amount      = 0;
          $trans_wallet = get_wallet($user->id, defaultCurr(), 1);
          $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
          $trans->charge      = $chargefee->data->fixed_charge;
          $trans->type        = '-';
          $trans->remark      = 'account-open';
          $trans->details     = trans('Wallet Create');
          $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
          $trans->save();

          $def_currency = Currency::findOrFail(defaultCurr());
          mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $def_currency->code, 'type'=>'Current', 'date_time'=> dateFormat($trans->created_at)], $user);

          user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
          user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
       }

       $wallet->balance += $voucher->amount;
       $wallet->update();

       $trnx              = new Transaction();
       $trnx->trnx        = str_rand();
       $trnx->user_id     = auth()->id();
       $trnx->user_type   = 1;
       $trnx->currency_id = $wallet->currency->id;
       $trnx->wallet_id   = $wallet->id;
       $trnx->amount      = $voucher->amount;
       $trnx->charge      = 0;
       $trnx->type        = '+';
       $trnx->remark      = 'reedem_voucher';
       $trnx->details     = trans('Voucher reedemed');
       $trnx->data        = '{"sender":"Vocher System", "receiver":"'.($user->company_name ?? $user->name).'"}';
       $trnx->save();

       $voucher->status = 1;
       $voucher->reedemed_by = auth()->id();
       $voucher->update();

       return back()->with('success','Voucher reedemed successfully');

    }

    public function reedemHistory()
    {
        $data['history'] = Voucher::where('status',1)->where('reedemed_by',auth()->id())->paginate(20);
        return view('user.voucher.history',$data);
    }
}
