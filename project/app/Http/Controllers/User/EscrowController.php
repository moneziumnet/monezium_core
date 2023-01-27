<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\Escrow;
use App\Models\Wallet;
use App\Models\Dispute;
use App\Models\Currency;
use App\Models\Charge;
use App\Models\PlanDetail;
use App\Models\Transaction;
use App\Helpers\MediaHelper;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;

class EscrowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['escrows'] = Escrow::where('user_id',auth()->id())->latest()->paginate(15);
        $data['wallets'] = Wallet::where('user_id',auth()->id())->where('wallet_type',5)->with('currency')->get();
        return view('user.escrow.index',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['wallets'] = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('balance', '>', 0)->where('wallet_type',5)->get();
        return view('user.escrow.create',$data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver'  => 'required|email',
            'wallet_id' => 'required|integer',
            'amount'    => 'required|numeric|gt:0',
            'description'    => 'required'
        ],
        [
            'wallet_id.required' => 'Wallet is required'
        ]);


        $receiver = User::where('email',$request->receiver)->first();
        if(!$receiver) return back()->with('error','Recipient not found');

        $senderWallet = Wallet::where('id',$request->wallet_id)->where('user_type',1)->where('user_id',auth()->id())->first();

        if(!$senderWallet) return back()->with('error','Your wallet not found');

        $currency = Currency::findOrFail($senderWallet->currency->id);
        $rate = getRate($currency);
        $user= auth()->user();
        $transaction_global_cost = 0;
        $transaction_global_fee = check_global_transaction_fee($request->amount/$rate, $user, 'escrow');
        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'escrow')->first();
        if ($request->amount < $global_range->min || $request->amount > $global_range->max) {
            return redirect()->back()->with('error','Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min );
        }
        if($transaction_global_fee)
        {
            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/($rate*100)) * $transaction_global_fee->data->percent_charge;
        }
        $transaction_custom_cost = 0;
        if($user->referral_id != 0)
        {
            $transaction_custom_fee = check_custom_transaction_fee($request->amount/$rate, $user, 'escrow');
            if($transaction_custom_fee) {
                $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($request->amount/($rate*100)) * $transaction_custom_fee->data->percent_charge;
            }
        }

        $finalCharge = $transaction_global_cost+$transaction_custom_cost;
        if($request->charge_pay) $finalAmount =  $request->amount + $finalCharge*$rate;
        else  $finalAmount =  $request->amount;

        if($senderWallet->balance < $finalAmount) return back()->with('error','Insufficient balance.');

        $senderWallet->balance -= $finalAmount;
        user_wallet_increment(0, $currency->id, $transaction_global_cost*$rate, 9);
        $senderWallet->update();
        if($user->referral_id != 0){
            $remark = 'Make_Escrow_supervisor_fee';
            if (check_user_type_by_id(4, $user->referral_id)) {
                user_wallet_increment($user->referral_id, $currency->id, $transaction_custom_cost*$rate, 6);
                $trans_wallet = get_wallet($user->referral_id, $currency->id, 6);
            }
            elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                $remark = 'Make_Escrow_manager_fee';
                user_wallet_increment($user->referral_id, $currency->id, $transaction_custom_cost*$rate, 10);
                $trans_wallet = get_wallet($user->referral_id, $currency->id, 10);
            }
            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $user->referral_id;
            $trans->user_type   = 1;
            $trans->currency_id = $currency->id;
            $trans->amount      = $transaction_custom_cost*$rate;
            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
            $trans->charge      = 0;
            $trans->type        = '+';
            $trans->remark      = $remark;
            $trans->details     = trans('Make Escrow');
            $trans->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.(User::findOrFail($user->referral_id)->company_name ?? User::findOrFail($user->referral_id)->name).'", "description": "'.$request->description.'"}';
            $trans->save();
        }

        $escrow               = new Escrow();
        $escrow->trnx         = str_rand();
        $escrow->user_id      = auth()->id();
        $escrow->recipient_id = $receiver->id;
        $escrow->description  = $request->description;
        $escrow->amount       = $request->amount;
        $escrow->pay_charge   = $request->charge_pay ? 1 : 0;
        $escrow->charge       = $finalCharge*$rate;
        $escrow->currency_id  = $currency->id;
        $escrow->save();

        $trnx              = new Transaction();
        $trnx->trnx        = $escrow->trnx;
        $trnx->user_id     = auth()->id();
        $trnx->user_type   = 1;
        $trnx->currency_id = $currency->id;
        $trnx->wallet_id   = $senderWallet->id;
        $trnx->amount      = $finalAmount;
        $trnx->charge      = $finalCharge*$rate;
        $trnx->remark      = 'make_escrow';
        $trnx->type        = '-';
        $trnx->details     = trans('Made escrow to '). $receiver->email;
        $trnx->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.($receiver->company_name ?? $receiver->name).'", "description": "'.$request->description.'"}';
        $trnx->save();
        send_notification(auth()->id(), 'New Escrow has been requested by '.auth()->user()->name.'. Please check.', route('admin.escrow.onHold'));

        return redirect(route('user.escrow.index'))->with('message','Escrow has been created successfully');
    }

    public function calcharge($amount)
        {
        $user= auth()->user();
        $transaction_global_cost = 0;
        $transaction_global_fee = check_global_transaction_fee($amount, $user, 'escrow');
        if($transaction_global_fee)
        {
            $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($amount/100) * $transaction_global_fee->data->percent_charge;
        }
        $transaction_custom_cost = 0;
        if(check_user_type(4))
        {
            $transaction_custom_fee = check_custom_transaction_fee($amount, $user, 'escrow');
            if($transaction_custom_fee) {
                $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($amount/100) * $transaction_custom_fee->data->percent_charge;
            }
        }

        $finalCharge = $transaction_global_cost+$transaction_custom_cost;
        return $finalCharge;
    }

    public function disputeForm($id)
    {
        $escrow = Escrow::where('id',$id)->firstOrFail();
        if (auth()->id() != $escrow->recipient_id && auth()->id() != $escrow->user_id){
            return back()->with('error','Invalid access');
        }

        if(url()->previous() == url('user/escrow-pending')) session()->put('route',route('user.escrow.pending'));
        elseif(url()->previous() == url('user/my-escrow'))  session()->forget('route');

        $messages = Dispute::where('escrow_id',$escrow->id)->with('user')->get();
        $data['escrow'] = $escrow;
        $data['messages'] = $messages;

        return view('user.escrow.dispute',$data);
    }

    public function disputeStore(Request $request,$escrow_id)
    {
        $request->validate(['message'=>'required','file' => 'mimes:png,jpeg,jpg|max:5186']);
        $escrow = Escrow::where('id',$escrow_id)->firstOrFail();
        if (auth()->id() != $escrow->recipient_id && auth()->id() != $escrow->user_id){
            return back()->with('error','Invalid access');
        }
        if($escrow->status == 4) return back()->with('error','Dispute has been closed');

        $escrow->status = 3;
        if($escrow->dispute_created == null) $escrow->dispute_created = auth()->id();
        $escrow->save();

        $dispute = new Dispute;
        $dispute->user_id = auth()->id();
        $dispute->escrow_id = $escrow_id;
        $dispute->message = $request->message;
        if($request->file) $dispute->file = MediaHelper::handleMakeImage($request->file);
        $dispute->save();
        send_notification(auth()->id(), 'Dispute about Escrow has been created by '.auth()->user()->name.'. Please check.', route('admin.escrow.disputed'));

        return back()->with('message','Replied submitted');
    }

    public function fileDownload($id)
    {
        $dispute = Dispute::findOrFail($id);
        $filepath = 'assets/images/'.$dispute->file;
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);
        $fileName =  @$dispute->user->email.'_file.'.$extension;
        header('Content-type: application/octet-stream');
        header("Content-Disposition: attachment; filename=".$fileName);
        while (ob_get_level()) {
            ob_end_clean();
        }
        readfile($filepath);
    }

    public function release($id)
    {

        $escrow = Escrow::where('id',$id)->where('user_id',auth()->id())->whereIn('status',[0,3])->first();
        $recipient = User::findOrFail($escrow->recipient_id);
        $recipientWallet = Wallet::where('user_id',$recipient->id)
                            ->where('user_type',1)
                            ->where('currency_id',$escrow->currency_id)
                            ->where('wallet_type', 5)
                            ->first();

        if(!$recipientWallet){
            $gs = Generalsetting::first();
            $recipientWallet =  Wallet::create(
                [
                    'user_id'      => $recipient->id,
                    'user_type'    => 1,
                    'currency_id'  => $escrow->currency_id,
                    'balance'      => 0,
                    'wallet_type' => 5,
                    'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
                ]
            );
            $user = User::findOrFail($recipient->id);

            $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
            if(!$chargefee) {
                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
            }

            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $recipient->id;
            $trans->user_type   = 1;
            $trans->currency_id = defaultCurr();
            $trans->amount      = $chargefee->data->fixed_charge;
            $trans_wallet = get_wallet($recipient->id, defaultCurr());
            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
            $trans->charge      = 0;
            $trans->type        = '-';
            $trans->remark      = 'wallet_create';
            $trans->details     = trans('Wallet Create');
            $trans->data        = '{"sender":"'.($recipient->company_name ?? $recipient->name).'", "receiver":"'.$gs->disqus.'", "description": "'.$escrow->description.'"}';
            $trans->save();

            user_wallet_decrement($recipient->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
            user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
        }

        $amount = $escrow->amount - $escrow->charge;

        $recipientWallet->balance += $amount;
        $recipientWallet->update();

        $trnx              = new Transaction();
        $trnx->trnx        = $escrow->trnx;
        $trnx->user_id     = $recipient->id;
        $trnx->user_type   = 1;
        $trnx->currency_id = $escrow->currency_id;
        $trans->wallet_id   = $recipientWallet->id;
        $trnx->amount      = $amount;
        $trnx->charge      = $escrow->pay_charge == 0 ? $escrow->charge : 0;
        $trnx->remark      = 'make_escrow';
        $trnx->type        = '+';
        $trnx->details     = trans('Received escrow money '). $recipient->email;
        $trnx->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.($recipient->company_name ?? $recipient->name).'", "description": "'.$escrow->description.'"}';
        $trnx->save();

        $escrow->status = 1;
        $escrow->save();
        send_notification($recipient->id, 'Holding Escrow has been released by '.auth()->user()->name.'. Please check.', route('admin.escrow.manage'));

        return back()->with('message','Escrow has been released');

    }

    public function pending()
    {
        $data['escrows'] = Escrow::where('recipient_id',auth()->id())->latest()->paginate(15);
        return view('user.escrow.pending',$data);
    }
}
