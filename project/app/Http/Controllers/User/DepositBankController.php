<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Str;
use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Models\DepositBank;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Auth;
use Illuminate\Support\Facades\DB;



class DepositBankController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $data['deposits'] = DepositBank::orderby('id','desc')->whereUserId(auth()->id())->paginate(10);
        return view('user.depositbank.index',$data);
    }

    public function create(){
        $data['subinstitude'] = Admin::where('id', '!=', 1)->orderBy('id')->get();
        $data['availableGatways'] = ['flutterwave','authorize.net','razorpay','mollie','paytm','instamojo','stripe','paypal'];
        $data['gateways'] = PaymentGateway::OrderBy('id','desc')->whereStatus(1)->get();
        $data['defaultCurrency'] = Currency::where('is_default',1)->first();

        return view('user.depositbank.create',$data);
    }

    public function list(Request $request) {
        return DB::table('sub_ins_banks')->where('ins_id', $request->id)->whereStatus(1)->get();
    }

    public function store(Request $request){

        $currency = Currency::where('id',$request->currency_id)->first();
        $amountToAdd = $request->amount/$currency->rate;
        $txnid = Str::random(4).time();
        $deposit = new DepositBank();
        $deposit['deposit_number'] = Str::random(12);
        $deposit['user_id'] = auth()->id();
        $deposit['currency_id'] = $request->currency_id;
        $deposit['amount'] = $amountToAdd;
        $deposit['method'] = $request->method;
        $deposit['txnid'] = $request->txnid;
        $deposit['status'] = "pending";
        $deposit->save();


        $gs =  Generalsetting::findOrFail(1);
        $user = auth()->user();
        if($gs->is_smtp == 1)
        {
            $data = [
                'to' => $user->email,
                'type' => "Deposit",
                'cname' => $user->name,
                'oamount' => $amountToAdd,
                'aname' => "",
                'aemail' => "",
                'wtitle' => "",
            ];

            $mailer = new GeniusMailer();
            $mailer->sendAutoMail($data);
        }
        else
        {
           $to = $user->email;
           $subject = " You have deposited successfully.";
           $msg = "Hello ".$user->name."!\nYou have invested successfully.\nThank you.";
           $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
           mail($to,$subject,$msg,$headers);
        }

        return redirect()->route('user.depositbank.create')->with('success','Deposit amount '.$request->amount.' ('.$currency->code.') successfully!');
    }


}