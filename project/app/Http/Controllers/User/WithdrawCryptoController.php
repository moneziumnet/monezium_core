<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Str;
use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Models\CryptoWithdraw;
use App\Models\Currency;
use App\Models\PlanDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SubInsBank;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class WithdrawCryptoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $data['withdraws'] = CryptoWithdraw::orderby('id','desc')->whereUserId(auth()->id())->paginate(10);
        return view('user.withdrawcrypto.index',$data);
    }

    public function create(){
        $data['cryptocurrencies'] = Currency::whereType(2)->get();
        return view('user.withdrawcrypto.create',$data);
    }


    public function store(Request $request){

        $currency = Currency::where('id',$request->currency_id)->first();
        $userBalance = user_wallet_balance($request->user_id,$request->currency_id,8);
        $amountToAdd = $request->amount/$currency->rate;
        $user = auth()->user();
        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'withdraw')->first();
        $dailywithdraw = CryptoWithdraw::where('user_id', $user->id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus('complete')->sum('amount');
        $monthlywithdraw = CryptoWithdraw::where('user_id', $user->id)->whereMonth('created_at', '=', date('m'))->whereStatus('complete')->sum('amount');

        if ( $amountToAdd < $global_range->min ||  $amountToAdd > $global_range->max) {
           return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value (USD) is '.$global_range->max.' and Min value(USD) is '.$global_range->min );

        }

        if($request->amount > $userBalance){
            return redirect()->back()->with('unsuccess','Insufficient Account Balance.');
        }

        if($dailywithdraw/$currency->rate > $global_range->daily_limit){
            return redirect()->back()->with('unsuccess','Daily withdraw limit over.');
        }

        if($monthlywithdraw/$currency->rate > $global_range->monthly_limit){
            return redirect()->back()->with('unsuccess','Monthly withdraw limit over.');
        }



        $withdraw = new CryptoWithdraw();
        $input = $request->all();

        $withdraw->fill($input)->save();



        return redirect()->route('user.cryptowithdraw.create')->with('success','Withdraw amount '.$request->amount.' ('.$currency->code.') successfully!');
    }


}
