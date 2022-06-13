<?php

namespace App\Http\Controllers\User;

use App\Models\Deposit;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use App\Http\Controllers\Controller;

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
        $data['availableGatways'] = ['flutterwave','authorize.net','razorpay','mollie','paytm','instamojo','stripe','paypal'];
        $data['gateways'] = PaymentGateway::OrderBy('id','desc')->whereStatus(1)->get();
        $data['defaultCurrency'] = Currency::where('is_default',1)->first();

        return view('user.deposit.create',$data);
    }
}
