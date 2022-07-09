<?php

namespace App\Http\Controllers\User;

use App\Models\Deposit;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Auth;
use Illuminate\Support\Facades\DB;



class DepositController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $data['deposits'] = Deposit::orderby('id','desc')->whereUserId(auth()->id())->paginate(10);
        return view('user.deposit.index',$data);
    }

    public function create(){
        $data['subinstitude'] = Admin::where('id', '!=', 1)->orderBy('id')->get();
        $data['availableGatways'] = ['flutterwave','authorize.net','razorpay','mollie','paytm','instamojo','stripe','paypal'];
        $data['gateways'] = PaymentGateway::OrderBy('id','desc')->whereStatus(1)->get();
        $data['defaultCurrency'] = Currency::where('is_default',1)->first();

        return view('user.deposit.create',$data);
    }

    public function gateway(Request $request) {
        return DB::table('payment_gateways')->where('subint_id', $request->id)->whereStatus(1)->get();
    }
}
