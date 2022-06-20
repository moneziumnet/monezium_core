<?php

namespace App\Http\Controllers\User;

use App\Models\Wallet;
use App\Models\Voucher;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
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
       
        $data['wallets'] = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('balance', '>', 0)->get();
        $data['charge'] = charge('create-voucher');
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
        
        $charge = charge('create-voucher');
        $rate = $wallet->currency->rate;

        if($request->amount <  $charge->minimum * $rate || $request->amount >  $charge->maximum * $rate){
            return back()->with('error','Please follow the limit');
        }

        $finalCharge = chargeCalc($charge,$request->amount,$rate);
        $finalAmount = numFormat($request->amount + $finalCharge);

        $commission  = ($request->amount * $charge->commission)/100;
      
        $voucher = new Voucher();
        $voucher->user_id = auth()->id();
        $voucher->currency_id = $wallet->currency_id;
        $voucher->amount = $request->amount;
        $voucher->code = randNum(10).'-'.randNum(10);
        $voucher->save();
        $userBalance = user_wallet_balance(auth()->id(), $wallet->currency_id);
        if($finalAmount > $userBalance) return back()->with('error','Wallet has insufficient balance');
        
        user_wallet_decrement(auth()->id(),$wallet->currency_id,$finalAmount);
        // $wallet->balance -=  $finalAmount;
        // $wallet->save();

        $trnx              = new Transaction();
        $trnx->trnx        = str_rand();
        $trnx->user_id     = auth()->id();
        $trnx->user_type   = 1;
        $trnx->currency_id = $wallet->currency->id;
        $trnx->amount      = $finalAmount;
        $trnx->charge      = $finalCharge;
        $trnx->remark      = 'create_voucher';
        $trnx->type        = '-';
        $trnx->details     = trans('Voucher created');
        $trnx->save();

        user_wallet_increment(auth()->id(),$wallet->currency_id,$commission);
        // $wallet->balance +=  $commission;
        // $wallet->save();

        $commissionTrnx              = new Transaction();
        $commissionTrnx->trnx        = $trnx->trnx;
        $commissionTrnx->user_id     = auth()->id();
        $commissionTrnx->user_type   = 1;
        $commissionTrnx->currency_id = $wallet->currency->id;
        $commissionTrnx->amount      = $commission;
        $commissionTrnx->charge      = 0;
        $commissionTrnx->remark      = 'voucher_commission';
        $commissionTrnx->type        = '+';
        $commissionTrnx->details     = trans('Voucher commission');
        $commissionTrnx->save();

        return back()->with('success','Voucher has been created successfully');

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
       
       $wallet = Wallet::where('currency_id',$voucher->currency_id)->where('user_id',auth()->id())->first();
       if(!$wallet){
          $wallet = Wallet::create([
              'user_id' => auth()->id(),
              'user_type' => 1,
              'currency_id' => $voucher->currency_id,
              'balance'   => 0
          ]);
       }

       $wallet->balance += $voucher->amount;
       $wallet->update();

       $trnx              = new Transaction();
       $trnx->trnx        = str_rand();
       $trnx->user_id     = auth()->id();
       $trnx->user_type   = 1;
       $trnx->currency_id = $wallet->currency->id;
       $trnx->amount      = $voucher->amount;
       $trnx->charge      = 0;
       $trnx->type        = '+';
       $trnx->remark      = 'reedem_voucher';
       $trnx->details     = trans('Voucher reedemed');
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
