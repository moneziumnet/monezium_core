<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Str;
use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Models\CryptoDeposit;
use App\Models\Currency;
use App\Models\PlanDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SubInsBank;
use Auth;
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
            if ($user->two_fa_code != $request->otp_code) {
                return redirect()->back()->with('unsuccess','Verification code is not matched.');
            }
        }
        $rules = [
            'proof' => 'required|mimes:png,jpg,gif'
        ];


        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with('unsuccess',$validator->getMessageBag()->toArray()['proof'][0]);
        }

        $currency = Currency::where('id',$request->currency_id)->first();
        $amountToAdd = $request->amount/$currency->rate;
        $user = auth()->user();
        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'deposit')->first();
        $dailydeposit = CryptoDeposit::where('user_id', $user->id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus('complete')->sum('amount');
        $monthlydeposit = CryptoDeposit::where('user_id', $user->id)->whereMonth('created_at', '=', date('m'))->whereStatus('complete')->sum('amount');

        if ( $amountToAdd < $global_range->min ||  $amountToAdd > $global_range->max) {
           return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value(USD) is '.$global_range->max.' and Min value(USD) is '.$global_range->min );

        }


        if($dailydeposit/$currency->rate > $global_range->daily_limit){
            return redirect()->back()->with('unsuccess','Daily deposit limit over.');
        }

        if($monthlydeposit/$currency->rate > $global_range->monthly_limit){
            return redirect()->back()->with('unsuccess','Monthly deposit limit over.');
        }



        $deposit = new CryptoDeposit();
        $input = $request->all();
        if ($file = $request->file('proof'))
        {
            $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/doc',$name);
            $input['proof'] = $name;
        }

        $deposit->fill($input)->save();

        $gs =  Generalsetting::findOrFail(1);
        $user = auth()->user();

           $to = $user->email;
           $subject = " You have deposited the crypto successfully.";
           $msg = "Hello ".$user->name."!\nYou have invested successfully.\nThank you.";
           $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
           mail($to,$subject,$msg,$headers);

        return redirect(route('user.cryptodeposit.index'))->with('success','Deposit amount '.$request->amount.' ('.$currency->code.') successfully!');
    }

    public function getcurrency(Request $request ) {
        $data = Currency::findOrFail($request->id);
        return $data->address;
    }

}
