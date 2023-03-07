<?php

namespace App\Http\Controllers\User;

use Validator;
use App\Models\User;
use App\Models\BankPlan;
use App\Models\Currency;
use App\Models\Wallet;
use App\Models\SaveAccount;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Classes\GoogleAuthenticator;
use Illuminate\Http\Request;

use App\Models\Generalsetting;
use App\Models\BalanceTransfer;
use App\Http\Controllers\Controller;
use App\Models\MerchantShop;
use App\Models\MerchantWallet;

class MerchantSendController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(){

        $wallets = MerchantWallet::where('merchant_id', auth()->id())
            ->where('balance', '>', 0)
            ->with('currency')
            ->get();

        $shops = MerchantWallet::select('shop_id')
            ->where('merchant_id', auth()->id())
            ->where('balance', '>', 0)
            ->groupBy('shop_id')
            ->with('shop')
            ->get();

        return view('user.merchant.sendmoney.create',compact('wallets', 'shops'));
    }

    public function store(Request $request){

        $request->validate([
            'wallet_id'         => 'required',
            'shop_id'           => 'required',
            'amount'            => 'required|numeric|min:0',
        ]);

        $user = auth()->user();

        $wallet = MerchantWallet::where('id',$request->wallet_id)->with('currency')->first();

        if($request->amount <= 0){
            return redirect()->back()->with('unsuccess','Request Amount should be greater than 0.00!');
        }

        if($request->amount > $wallet->balance){
            return redirect()->back()->with('unsuccess','Insufficient Balance.');
        }

        if($wallet->merchant_id != $user->id){
            return redirect()->back()->with('unsuccess','You are not the owner of this wallet.');
        }

        $txnid = Str::random(4).time();

        $trans = new Transaction();
        $trans->trnx        = $txnid;
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id = $wallet->currency_id;
        $trans->amount      = $request->amount;
        $trans->charge      = 0;
        $trans->type        = '-';
        $trans->remark      = 'Merchant to own';
        $trans->details     = trans('Merchant to own');
        $trans->data         = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.($user->company_name ?? $user->name).'"}';
        $trans->save();

        $trans = new Transaction();
        $trans->trnx        = $txnid;
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id = $wallet->currency_id;
        $trans->amount      = $request->amount;
        $trans_wallet       = get_wallet($user->id, $wallet->currency_id);
        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
        $trans->charge      = 0;
        $trans->type        = '+';
        $trans->remark      = 'Merchant to own';
        $trans->details     = trans('Merchant to own');
        $trans->data         = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.($user->company_name ?? $user->name).'"}';
        $trans->save();

        merchant_shop_wallet_decrement($user->id, $wallet->currency_id, $request->amount, $request->shop_id);
        user_wallet_increment($user->id, $wallet->currency_id, $request->amount, 1);

        return redirect()->back()->with('success','Exchange merchant to own wallet successfully.');
    }

    public function savedUser($no){
        // if(auth()->user()->twofa)
        // {
            $ga = new GoogleAuthenticator();
            $data['secret'] = $ga->createSecret();
            $wallets = Wallet::where('user_id',auth()->id())->with('currency')->get();
            $data['wallets'] = $wallets;
            $data['savedUser'] = User::whereEmail($no)->first();
            $data['saveAccounts'] = SaveAccount::whereUserId(auth()->id())->orderBy('id','desc')->get();

            return view('user.merchant.sendmoney.create',$data);
        // }else{
        //     return redirect()->route('user.show2faForm')->with('unsuccess','You must be enable 2FA Security');
        // }
    }

    public function success(){
        if(session('saveData') && session('sendstatus') == 1){
            $data['data'] = session()->get('saveData');
            $data['user_id'] = auth()->user()->id;

            session(['sendstatus'=>0]);
            return view('user.merchant.sendmoney.success',$data);
        }else{
            session(['sendstatus'=>0]);
            $data['savedUser'] =  NULL;
            $data['saveAccounts'] = SaveAccount::whereUserId(auth()->id())->orderBy('id','desc')->get();

            return view('user.merchant.sendmoney.create',$data);
        }
    }

    public function saveAccount(Request $request){
        $savedUser = SaveAccount::whereUserId(auth()->id())->where('receiver_id',$request->receiver_id)->first();

        if($savedUser){
            return redirect()->route('user.merchant.send.money.create')->with('success','Already Saved.');
        }
        $data = new SaveAccount();

        $data->user_id = $request->user_id;
        $data->receiver_id = $request->receiver_id;
        $data->save();

        return redirect()->route('user.merchant.send.money.create')->with('success','Money Send Successfully');
    }

    public function cancle(){
        return redirect()->route('user.merchant.send.money.create')->with('success','Money Send Successfully');
    }


}
