<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\DpsPlan;
use App\Models\InstallmentLog;
use App\Models\Transaction;
use App\Models\UserDps;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Models\FdrPlan;
use App\Models\UserFdr;


class UserInvestmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $data['dps_plans'] = DpsPlan::orderBy('id','desc')->whereStatus(1)->orderby('id','desc')->paginate(12);
        $data['currencylist'] = Currency::whereStatus(1)->where('type', 1)->get();
        $data['dps'] = UserDps::whereUserId(auth()->id())->orderby('id','desc')->paginate(10);

        $data['fdr_plans'] = FdrPlan::orderBy('id','desc')->whereStatus(1)->orderby('id','desc')->paginate(12);
        $data['fdr'] = UserFdr::whereUserId(auth()->id())->orderby('id','desc')->paginate(10);

        $wallets = Wallet::where('user_id',auth()->id())->where('wallet_type',3)->with('currency')->get();
        $data['wallets'] = $wallets;


        return view('user.investment.index',$data);
    }

}
