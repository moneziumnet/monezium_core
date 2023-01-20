<?php

namespace App\Http\Controllers\Admin;

use App\Models\Escrow;
use App\Models\Wallet;
use App\Models\Dispute;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Charge;
use App\Helpers\MediaHelper;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;

class ManageEscrowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $title = "Manage Escrow";
        $escrows = Escrow::with(['user','recipient','currency'])->latest()->paginate(15);
        return view('admin.escrow.index',compact('escrows','title'));
    }

    public function details($id)
    {
        $escrow = Escrow::with(['user','recipient','currency'])->findOrFail($id);
        $messages = Dispute::where('escrow_id',$escrow->id)->with('user')->get();
        return view('admin.escrow.details',compact('escrow','messages'));
    }

    public function onHold()
    {
        $title = "On Hold Escrows";
        $escrows = Escrow::whereStatus(0)->with(['user','recipient','currency'])->latest()->paginate(15);
        return view('admin.escrow.index',compact('escrows','title'));
    }
    public function disputed()
    {
        $title = "Disputed Escrows";
        $escrows = Escrow::whereStatus(3)->with(['user','recipient','currency'])->latest()->paginate(15);
        return view('admin.escrow.index',compact('escrows','title'));
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

    public function disputeStore(Request $request,$escrow_id)
    {
        $request->validate(['message'=>'required','file' => 'mimes:png,jpeg,jpg|max:5186']);

        $escrow = Escrow::findOrFail($escrow_id);
        if($escrow->status == 4){
            return back()->with('error','Dispute has been closed');
        }

        $dispute = new Dispute;
        $dispute->admin_id = admin()->id;
        $dispute->escrow_id = $escrow_id;
        $dispute->message = $request->message;
        if($request->file){
            $dispute->file = MediaHelper::handleMakeImage($request->file);
        }
        $dispute->save();
        return back()->with('success','Reply submitted');
    }

    public function returnPayment(Request $request)
    {
        $request->validate(['id'=>'required','escrow_id'=>'required']);

        $escrow = Escrow::findOrFail($request->escrow_id);
        $wallet = Wallet::where('user_id',$request->id)->where('user_type',1)->where('currency_id',$escrow->currency_id)->where('wallet_type', 5)->first();
        $gs = Generalsetting::first();
        if(!$wallet){
            $wallet = Wallet::create([
                'user_id' => $request->id,
                'user_type' => 1,
                'currency_id' => $escrow->currency_id,
                'balance' => 0,
                'wallet_type' => 5,
                'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
            ]);

            $user = User::findOrFail($request->id);

            $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
            if(!$chargefee) {
                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
            }

            user_wallet_decrement($request->id, defaultCurr(), $chargefee->data->fixed_charge, 5);
            user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);

            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $request->id;
            $trans->user_type   = 1;
            $trans->currency_id = defaultCurr();
            $trans->amount      = $chargefee->data->fixed_charge;

            $trans_wallet       = get_wallet($request->id, defaultCurr(), 5);
            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

            $trans->charge      = 0;
            $trans->type        = '-';
            $trans->remark      = 'wallet_create';
            $trans->details     = trans('Wallet Create');

            $trans->data        = '{"sender":"'.(User::findOrFail($request->id)->company_name ?? User::findOrFail($request->id)->name ).'", "receiver":"'.$gs->disqus.'"}, "description":"'.$escrow->description.'"}';
            $trans->save();
        }

        $wallet->balance += $escrow->amount;
        $wallet->update();


        $trnx              = new Transaction();
        $trnx->trnx        = str_rand();
        $trnx->user_id     = $request->id;
        $trnx->user_type   = 1;
        $trnx->currency_id = $escrow->currency_id;
        $trnx->amount      = $escrow->amount;
        $trnx->charge      = 0;
        $trnx->wallet_id   = $wallet->id;
        $trnx->type        = '+';
        $trnx->remark      = 'escrow_return';
        $trnx->details     = trans('Escrow fund returned');
        $trnx->data        = '{"sender":"Escrow System", "receiver":"'.(User::findOrFail($request->id)->company_name ?? User::findOrFail($request->id)->name ).'"}';
        $trnx->save();

        $escrow->returned_to = @$wallet->user->email;
        $escrow->status = 4;
        $escrow->update();

        @mailSend('escrow_return',['amount'=>amount($escrow->amount,$escrow->currency->type,2), 'trnx'=> $trnx->trnx,'curr' => $escrow->currency->code,'data_time'=> dateFormat($trnx->created_at)], $wallet->user);

        return back()->with('success','Payment has been returned to '.@$wallet->user->email);

    }

    public function close($id)
    {
        $escrow = Escrow::findOrFail($id);
        $escrow->status = 4;
        $escrow->save();
        return back()->with('success','Escrow has been closed');
    }
}
