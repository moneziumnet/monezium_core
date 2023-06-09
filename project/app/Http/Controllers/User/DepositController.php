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
        $data['user'] = auth()->user();

        return view('user.deposit.create',$data);
    }

    public function gateway(Request $request) {
        return DB::table('payment_gateways')->where('subins_id', $request->id)->whereStatus(1)->get();
    }

    public function gatewaycurrency(Request $request) {
        $currency['id'] = DB::table('payment_gateways')->where('subins_id', $request->id)->where('keyword', $request->keyword)->whereStatus(1)->first()->currency_id;
        $res = [];
        foreach (json_decode($currency['id']) as $value) {
            $code =  Currency::where('id',$value)->first();
            array_push($res,$code);
        }
        return $res;
    }
}
