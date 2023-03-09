<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Str;

use App\Models\Generalsetting;
use App\Models\CryptoDeposit;
use App\Models\Currency;
use App\Models\PlanDetail;
use App\Models\Wallet;
use App\Classes\GoogleAuthenticator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SubInsBank;
use Auth;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class DepositCryptoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $data['deposits'] = CryptoDeposit::orderby('id','desc')->whereUserId(auth()->id())->paginate(10);
        return view('user.depositcrypto.index',$data);
    }

    public function create(){
        $data['cryptocurrencies'] = Currency::whereType(2)->get();
        $data['user'] = auth()->user();
        return view('user.depositcrypto.create',$data);
    }


    public function store(Request $request){
        $user = auth()->user();
        if($user->paymentCheck('Crypto Incoming')) {
            if ($user->payment_fa != 'two_fa_google') {
                if ($user->two_fa_code != $request->otp_code) {
                    return redirect()->back()->with('unsuccess','Verification code is not matched.');
                }
            }
            else{
                $googleAuth = new GoogleAuthenticator();
                $secret = $user->go;
                $oneCode = $googleAuth->getCode($secret);
                if ($oneCode != $request->otp_code) {
                    return redirect()->back()->with('unsuccess','Verification code is not matched.');
                }
            }
        }

        $currency = Currency::where('id',$request->currency_id)->first();
        $rate = getRate($currency);
        $amountToAdd = $request->amount/$rate;
        $user = auth()->user();
        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'deposit')->first();
        $dailydeposit = CryptoDeposit::where('user_id', $user->id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus('complete')->sum('amount');
        $monthlydeposit = CryptoDeposit::where('user_id', $user->id)->whereMonth('created_at', '=', date('m'))->whereStatus('complete')->sum('amount');

        if ( $amountToAdd < $global_range->min ||  $amountToAdd > $global_range->max) {
           return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min );

        }


        if($dailydeposit/$rate > $global_range->daily_limit){
            return redirect()->back()->with('unsuccess','Daily deposit limit over.');
        }

        if($monthlydeposit/$rate > $global_range->monthly_limit){
            return redirect()->back()->with('unsuccess','Monthly deposit limit over.');
        }



        $deposit = new CryptoDeposit();
        $input = $request->all();

        $deposit->fill($input)->save();

        $gs =  Generalsetting::findOrFail(1);
        $user = auth()->user();
        mailSend('deposit_request',['amount'=>$deposit->amount, 'curr' => $currency->code, 'date_time'=>$deposit->created_at ,'type' => 'Crypto', 'method'=> $currency->code ], $user);

        return redirect(route('user.cryptodeposit.index'))->with('message','Deposit amount '.$request->amount.' ('.$currency->code.') successfully!');
    }

    public function getcurrency(Request $request ) {
        $data = Wallet::where('currency_id',$request->id)->where('wallet_type', 8)->where('user_id', auth()->id())->first();
        return $data->wallet_no;
    }

}
