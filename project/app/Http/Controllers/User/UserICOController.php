<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\IcoToken;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class UserICOController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $data['ico_tokens'] = IcoToken::orderBy('id', 'desc')->paginate(15);
        return view('user.ico.index', $data);
    }

    public function mytoken()
    {
        $data['ico_tokens'] = IcoToken::where('user_id',auth()->id())->orderBy('id', 'desc')->get();
        return view('user.ico.mytoken', $data);
    }

    public function edit($id)
    {
        $data['item'] = IcoToken::findOrFail($id);
        return view('user.ico.edit', $data);
    }

    public function details($id)
    {
        $data['item'] = IcoToken::findOrFail($id);
        return view('user.ico.detail', $data);
    }

    public function show_buy($id)
    {
        $ico_token = IcoToken::findOrFail($id);
        $wallets = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('wallet_type',1)->where('balance', '>', 0)->get();
        $currencies = Currency::whereStatus(1)->get();
        if (!isEnabledUserModule('Crypto'))
            $currencies = Currency::whereStatus(1)->where('type', 1)->get();
        $user = auth()->user();
        return view('user.ico.buy', compact('wallets','currencies', 'user', 'ico_token'));
    }

    public function buy($id, Request $request)
    {
        $request->validate([
            'from_wallet_id'    => 'required',
            'amount'            => 'required|numeric|min:1',
        ]);

        $ico_token = IcoToken::findOrFail($id);
        $wallet = Wallet::where('id',$request->from_wallet_id)->with('currency')->first();
        $currency_id = $wallet->currency->id;
        $user = auth()->user();
        $transaction_amount = $request->amount * $ico_token->price * getRate($wallet->currency);

        if(now()->gt($ico_token->end_date)){
            return redirect()->back()->with('error','Date Expired.');
        }

        if($ico_token->balance >= $ico_token->total_supply){
            return redirect()->back()->with('error','Run out of token.');
        }

        if($ico_token->status == 0) {
            return redirect()->back()->with('error','Status disabled.');
        }

        if($transaction_amount > user_wallet_balance(auth()->id(), $currency_id, $wallet->wallet_type)){
            return redirect()->back()->with('error','Insufficient Balance.');
        }

        // Change buyer balance
        user_wallet_increment($user->id, $ico_token->currency->id, $request->amount, 8);
        user_wallet_decrement($user->id, $currency_id, $transaction_amount, 1);

        // Increase seller balance
        user_wallet_increment($ico_token->user_id, $currency_id, $transaction_amount, 1);

        $ico_token->increment('balance', $request->amount);

        $trans = new Transaction();
        $trans->trnx = str_rand();
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id = $ico_token->currency->id;
        $trans->amount      = $request->amount;
        $trans_wallet = get_wallet($user->id, $ico_token->currency->id, 8);
        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
        $trans->charge      = 0;
        $trans->type        = '+';
        $trans->remark      = 'ico_token_buy';
        $trans->details     = trans('Buy ico token');
        $trans->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.($ico_token->user->company_name ?? $ico_token->user->name).'"}';
        $trans->save();

        $trans = new Transaction();
        $trans->trnx = str_rand();
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id = $currency_id;
        $trans_wallet = get_wallet($user->id, $currency_id, 1);
        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
        $trans->amount      = $transaction_amount;
        $trans->charge      = 0;
        $trans->type        = '-';
        $trans->remark      = 'ico_token_buy';
        $trans->details     = trans('Buy ico token');
        $trans->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.($ico_token->user->company_name ?? $ico_token->user->name).'"}';
        $trans->save();

        $trans = new Transaction();
        $trans->trnx = str_rand();
        $trans->user_id     = $ico_token->user->id;
        $trans->user_type   = 1;
        $trans->currency_id = $currency_id;
        $trans->amount      = $transaction_amount;
        $trans_wallet = get_wallet($ico_token->user_id, $currency_id, 1);
        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
        $trans->charge      = 0;
        $trans->type        = '+';
        $trans->remark      = 'ico_token_sell';
        $trans->details     = trans('Sell ico token');
        $trans->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.($ico_token->user->company_name ?? $ico_token->user->name).'"}';
        $trans->save();

        return redirect(route('user.ico'))->with('message','Buy token successfully.');
    }

    public function delete($id)
    {
        $data = IcoToken::findOrFail($id);
        $currency = Currency::findOrFail($data->currency_id);
        File::delete('assets/doc/'.$data->white_paper);
        $currency->delete();
        $data->delete();
        return  redirect()->back()->with('message','ICO Token has been deleted successfully');
    }

    public function store(Request $request)
    {
        $rules = [
            'whitepaper' => 'required|mimes:doc,docx,pdf'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with('error',$validator->getMessageBag()->toArray()['whitepaper'][0]);
        }

        $data = new IcoToken();
        if ($file = $request->file('whitepaper'))
        {
            $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/doc',$name);
        }
        $currency = Currency::where('code', $request->code)
            ->orWhere('symbol', $request->symbol)
            ->first();
        if($currency) {
            return redirect()->back()->with('error','The currency already exists.');
        } else {
            $currency = new Currency();
            $currency->symbol  = $request->symbol;
            $currency->code  = $request->code;
            $currency->address  = $request->address;
            $currency->curr_name = $request->name;
            $currency->rate = $request->price == 0 ? 0 : 1 / $request->price;
            $currency->type  = 2;
            $currency->save();
        }
        $data->name = $request->name;
        $data->user_id = auth()->id();
        $data->price = $request->price;
        $data->total_supply = $request->total_supply;
        $data->end_date = $request->end_date;
        $data->currency_id = $currency->id;
        $data->white_paper = $name;
        $data->save();

        mailSend('ico_create',[ 'date_time'=>$data->created_at, 'code' => $currency->code, 'symbol' => $currency->symbol, 'total_supply' => $data->total_supply, 'amount' => $data->price], auth()->user());

        send_notification(auth()->id(), 'New ICO token has been created by '.(auth()->user()->company_name ?? auth()->user()->name).'. Please check.', route('admin.ico.index'));
        send_staff_telegram('New ICO token has been created by '.(auth()->user()->company_name ?? auth()->user()->name).". Please check.\n".route('admin.ico.index'), 'ICO');

        return redirect()->back()->with('message','New ICO Token has been created successfully');
    }

    public function update($id, Request $request)
    {
        $rules = [
            'whitepaper' => 'mimes:doc,docx'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with('error',$validator->getMessageBag()->toArray()['whitepaper'][0]);
        }

        $data = IcoToken::find($id);
        if ($file = $request->file('whitepaper'))
        {
            $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/doc',$name);
            $data->white_paper = $name;
        }
        $currency = Currency::where('code', $request->code)
            ->orWhere('symbol', $request->symbol)
            ->first();
        if($currency->id != $data->currency_id && $currency) {
            return redirect()->back()->with('error','The currency already exists.');
        }
        if(!$currency || $currency && $currency->id == $data->currency_id) {
            if(!$currency)
                $currency = new Currency();
            $currency->symbol  = $request->symbol;
            $currency->code  = $request->code;
            $currency->address  = $request->address;
            $currency->curr_name = $request->name;
            $currency->rate = $request->price == 0 ? 0 : 1 / $request->price;
            $currency->type  = 2;
            $currency->save();
            $data->currency_id = $currency->id;
        }
        $data->name = $request->name;
        $data->user_id = auth()->id();
        $data->price = $request->price;
        $data->total_supply = $request->total_supply;
        $data->end_date = $request->end_date;
        $data->save();

        return redirect(route('user.ico.mytoken'))->with('message','The ICO Token has been updated successfully');
    }
}
