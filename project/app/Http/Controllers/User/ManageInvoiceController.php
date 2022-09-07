<?php

namespace App\Http\Controllers\User;

use Auth;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Charge;
use App\Models\InvItem;
use App\Models\Invoice;
use App\Models\Currency;
use App\Models\InvoiceBeneficiary;
use App\Models\Tax;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Models\InvoiceSetting;
use App\Http\Controllers\Controller;

class ManageInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['invoiceView']]);
    }

    public function index()
    {
        $data['invoices'] = Invoice::where('user_id',auth()->id())->latest()->paginate(15);
        return view('user.invoice.index',$data);
    }

    public function incoming_index()
    {
        $user = User::findOrFail(auth()->id());
        $data['invoices'] = Invoice::where('email',$user->email)->latest()->paginate(15);
        return view('user.invoice.incoming_index',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['currencies'] = Currency::where('status', 1)->get();
        $data['beneficiaries'] = InvoiceBeneficiary::where('user_id', auth()->id())->get();
        return view('user.invoice.create',$data);
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
            'currency'   => 'required',
            'item'       => 'required',
            'item.*'     => 'required',
            'amount'     => 'required',
            'amount.*'   => 'required|numeric|gt:0',
            'description' => 'required',
            'beneficiary_id' => 'required'
        ]);

        $currency = Currency::findOrFail($request->currency);

        $amount = array_sum($request->amount);
        $beneficiary = InvoiceBeneficiary::whereId($request->beneficiary_id)->first();
        $setting = InvoiceSetting::where('user_id', auth()->id())->first();
        $type = 'prefix_'.$request->type;
        $length = 'length_'.$request->type;

        $invoice = new Invoice();
        $invoice->user_id      = auth()->id();
        $invoice->number       = $setting->number_generator->$type.randNum($setting->number_generator->$length);
        $invoice->invoice_to   = $beneficiary->name;
        $invoice->email        = $beneficiary->email;
        $invoice->address      = $beneficiary->registration_no;
        $invoice->currency_id  = $currency->id;
        $invoice->charge       = 0;
        $invoice->type       = $request->type;
        $invoice->final_amount = $amount;
        $invoice->get_amount   = $amount;
        $invoice->beneficiary_id = $request->beneficiary_id;
        $invoice->description = $request->description;

        $data = [];
        if($request->hasfile('document'))
        {
           foreach($request->file('document') as $file)
           {
               $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
               $file->move('assets/doc', $name);
               array_push($data, $name);
           }
        }



        $invoice->documents = implode(",",$data);
        $invoice->save();

        $items = array_combine($request->item,$request->amount);
        $i=0;
        foreach($items as $item => $amount){
            $invItem             = new InvItem();
            $invItem->invoice_id = $invoice->id;
            $invItem->name       = $item;
            $invItem->amount	 = $amount;
            $invItem->tax_id    = $request->tax_id[$i];
            $invItem->save();
            $i++;
        }

        $route = route('invoice.view',encrypt($invoice->number));
        @email([

            'email'   => $invoice->email,
            "subject" => trans('Invoice Payment'),
            'message' => trans('Hello')." $invoice->invoice_to,<br/></br>".

                trans('You have pending payment of invoice')." <b>$invoice->number</b>.".trans('Please click the below link to complete your payment') .".<br/></br>".

                trans('Invoice details').": <br/></br>".

                trans('Amount')  .":  $amount $currency->code <br/>".
                trans('Payment Link')." :  <a href='$route' target='_blank'>".trans('Click To Payment')."</a><br/>".
                trans('Time')." : $invoice->created_at,

            "
        ]);

        return back()->with('message','Invoice has been created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $invoice = Invoice::findOrFail($id);
        $data['invoice'] = $invoice;

        // if($invoice->status == 1){
        //     return back()->with('error','Sorry! can\'t edit published invoice.');
        // }
        $data['currencies'] = Currency::where('status', 1)->get();
        $data['beneficiaries'] = InvoiceBeneficiary::where('user_id', auth()->id())->get();
        return view('user.invoice.edit',$data);
    }

    public function incoming_edit($id)
    {
        $invoice = Invoice::findOrFail($id);
        $data['invoice'] = $invoice;

        // if($invoice->status == 1){
        //     return back()->with('error','Sorry! can\'t edit published invoice.');
        // }
        $data['currencies'] = Currency::where('status', 1)->get();
        $data['beneficiaries'] = InvoiceBeneficiary::where('user_id', auth()->id())->get();
        return view('user.invoice.incoming_edit',$data);
    }

    public function incoming_update(Request $request, $id)
    {
        $request->validate([
            'currency'   => 'required',
            'item'       => 'required',
            'item.*'     => 'required',
            'amount'     => 'required',
            'amount.*'   => 'required|numeric|gt:0',
            'description' => 'required',
            'beneficiary_id' => 'required'
        ],['amount.*.gt'=>'Amount must be greater than 0']);

        $currency = Currency::findOrFail($request->currency);

        $beneficiary = InvoiceBeneficiary::whereId($request->beneficiary_id)->first();

        $invoice = Invoice::findOrFail($id);
        $invoice->user_id      = auth()->id();
        $invoice->invoice_to   = $beneficiary->name;
        $invoice->email        = $beneficiary->email;
        $invoice->address      = $beneficiary->registration_no;
        $invoice->currency_id  = $currency->id;
        $invoice->type       = $request->type;
        $invoice->charge       = 0;
        $invoice->final_amount = array_sum($request->amount);
        $invoice->get_amount   = array_sum($request->amount);
        $invoice->beneficiary_id = $request->beneficiary_id;
        $invoice->description = $request->description;
        $invoice->update();

        $invoice->items()->delete();
        $items = array_combine($request->item,$request->amount);
        $i=0;

        foreach($items as $item => $amount){
            $invItem             = new InvItem();
            $invItem->invoice_id = $invoice->id;
            $invItem->name       = $item;
            $invItem->amount	 = $amount;
            $invItem->tax_id    = $request->tax_id[$i];
            $invItem->save();
            $i++;
        }
        return back()->with('message','Invoice has been updated');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'currency'   => 'required',
            'item'       => 'required',
            'item.*'     => 'required',
            'amount'     => 'required',
            'amount.*'   => 'required|numeric|gt:0',
            'description' => 'required',
            'beneficiary_id' => 'required'
        ],['amount.*.gt'=>'Amount must be greater than 0']);

        $currency = Currency::findOrFail($request->currency);

        $beneficiary = InvoiceBeneficiary::whereId($request->beneficiary_id)->first();
        $setting = InvoiceSetting::where('user_id', auth()->id())->first();
        $type = 'prefix_'.$request->type;
        $length = 'length_'.$request->type;

        $invoice = Invoice::findOrFail($id);
        $invoice->user_id      = auth()->id();
        $invoice->invoice_to   = $beneficiary->name;
        $invoice->email        = $beneficiary->email;
        $invoice->number       = $setting->number_generator->$type.randNum($setting->number_generator->$length);
        $invoice->address      = $beneficiary->registration_no;
        $invoice->currency_id  = $currency->id;
        $invoice->type       = $request->type;
        $invoice->charge       = 0;
        $invoice->final_amount = array_sum($request->amount);
        $invoice->get_amount   = array_sum($request->amount);
        $invoice->beneficiary_id = $request->beneficiary_id;
        $invoice->description = $request->description;
        $data = [];
        if($request->file('document'))
        {
           foreach($request->file('document') as $file)
           {
               $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
               $file->move('assets/doc', $name);
               array_push($data, $name);
           }
        }



        $invoice->documents = implode(",",$data);
        $invoice->update();

        $invoice->items()->delete();
        $items = array_combine($request->item,$request->amount);
        $i=0;

        foreach($items as $item => $amount){
            $invItem             = new InvItem();
            $invItem->invoice_id = $invoice->id;
            $invItem->name       = $item;
            $invItem->amount	 = $amount;
            $invItem->tax_id    = $request->tax_id[$i];
            $invItem->save();
            $i++;
        }
        return back()->with('message','Invoice has been updated');
    }

    public function payStatus(Request $request)
    {
        $invoice = Invoice::findOrFail($request->id);
        if(!$invoice) return response(['error'=>'Invalid request']);

        if($invoice->payment_status == 1){
            $invoice->payment_status = 0;
            $invoice->update();
            return response(['unpaid'=>'Payment status changed to un-paid']);
        }else{
            $invoice->payment_status = 1;
            $invoice->update();
            return response(['paid'=>'Payment status changed to paid']);
        }


    }
    public function publishStatus(Request $request)
    {
        $invoice = Invoice::findOrFail($request->id);
        if(!$invoice) return response(['error'=>'Invalid request']);

        if($invoice->status == 1){
            $invoice->status = 0;
            $invoice->update();
            return response(['unpublish'=>trans('Status changed to un-published')]);
        }else{
            $invoice->status = 1;
            $invoice->update();
            return response(['publish'=>trans('Status changed to published')]);
        }

    }

    public function cancel($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->status = 2;
        $invoice->save();
        return redirect(route('user.invoice.index'))->with('success','Invoice has been cancelled');
    }

    public function invoiceView($number)
    {
        try {
            $invoice = Invoice::where('number',decrypt($number))->firstOrFail();
            $data['invoice'] = $invoice;
            $data['user'] = User::where('id',$data['invoice']->user_id)->first();
        } catch (\Throwable $th) {
            return back()->with('error','Something went wrong.');
        }

        if($invoice->status == 0) return back()->with('error','Invoice not published yet.');
        if($invoice->status == 2) return back()->with('error','Invoice has been cancelled.');
        return view('user.invoice.view',$data);

    }
    public function view($number)
    {
        $data['invoice'] = Invoice::where('number',$number)->firstOrFail();
        $data['user'] = User::where('id',$data['invoice']->user_id)->first();
        return view('user.invoice.invoice',$data);
    }

    public function sendToMail($id)
    {
        $invoice = Invoice::findOrFail($id);
        $currency = $invoice->currency;
        $amount = amount($invoice->final_amount,$currency->type,3);
        $route = route('invoice.view',encrypt($invoice->number));

        email([

            'email'   => $invoice->email,
            "subject" => trans('Invoice Payment'),
            'message' => trans('Hello')." $invoice->invoice_to,<br/></br>".

                trans('You have pending payment of invoice')." <b>$invoice->number</b>.".trans('Please click the below link to complete your payment') .".<br/></br>".

                trans('Invoice details').": <br/></br>".

                trans('Amount')  .":  $amount $currency->code <br/>".
                trans('Payment Link')." :  <a href='$route' target='_blank'>".trans('Click To Payment')."</a><br/>".
                trans('Time')." : $invoice->created_at,

            "
        ]);

        return back()->with('message','Invoice has been sent to the recipient');
    }

    public function invoicePayment($number)
    {
        try {
            $invoice = Invoice::where('number',decrypt($number))->firstOrFail();
            if($invoice->payment_status == 1){
                return back()->with('error','Invoice already been paid');
            }
            session()->put('invoice',encrypt($invoice));
        } catch (\Throwable $th) {
           return back()->with('error','Something went wrong');
        }

        if($invoice->user_id == auth()->id()){
            return back()->with('error','You can not pay your own invoice.');
        }

        return view('user.invoice.invoice_payment',compact('invoice'));
    }

    public function invoicePaymentSubmit(Request $request,$number)
    {
        if($request->payment == 'gateway'){
            return redirect(route('user.pay.invoice'));
        }
        elseif($request->payment == 'wallet'){
            try {
                $invoice = decrypt(session('invoice'));
            } catch (\Throwable $th) {
               return back()->with('error','Something went wrong');
            }

            $wallet = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('currency_id',$invoice->currency_id)->where('wallet_type', 1)->first();

            if(!$wallet){
                $gs = Generalsetting::first();
                $wallet =  Wallet::create([
                    'user_id'     => auth()->id(),
                    'user_type'   => 1,
                    'currency_id' => $invoice->currency_id,
                    'balance'     => 0,
                    'wallet_type' => 1,
                    'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
                ]);

                $user = User::findOrFail(auth()->id());

                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->first();

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = 1;
                $trans->amount      = $chargefee->data->fixed_charge;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'wallet_create';
                $trans->details     = trans('Wallet Create');
                $trans->data        = '{"sender":"'.$user->name.'", "receiver":"System Account"}';
                $trans->save();

                user_wallet_decrement($user->id, 1, $chargefee->data->fixed_charge, 1);
                user_wallet_increment(0, 1, $chargefee->data->fixed_charge, 9);
            }

            if($wallet->balance < $invoice->final_payment) {
                return back()->with('error','Insufficient balance to your wallet');
            }

            $wallet->balance -= $invoice->final_payment;
            $wallet->update();

            $trnx              = new Transaction();
            $trnx->trnx        = str_rand();
            $trnx->user_id     = auth()->id();
            $trnx->user_type   = 1;
            $trnx->currency_id = $invoice->currency_id;
            $trnx->wallet_id   = $wallet->id;
            $trnx->amount      = $invoice->final_amount;
            $trnx->charge      = 0;
            $trnx->remark      = 'invoice_payment';
            $trnx->invoice_num = $invoice->number;
            $trnx->type        = '-';
            $trnx->details     = trans('Payemnt to invoice : '). $invoice->number;
            $trnx->data        = '{"sender":"'.auth()->user()->name.'", "receiver":"'.User::findOrFail($invoice->user_id)->name.'"}';
            $trnx->save();

            $rcvWallet = Wallet::where('user_id',$invoice->user_id)->where('user_type',1)->where('currency_id',$invoice->currency_id)->where('wallet_type', 1)->first();

            if(!$rcvWallet){
                $gs = Generalsetting::first();
                $rcvWallet =  Wallet::create([
                    'user_id'     => $invoice->user_id,
                    'user_type'   => 1,
                    'currency_id' => $invoice->currency_id,
                    'balance'     => 0,
                    'wallet_type' => 1,
                    'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
                ]);

                $user = User::findOrFail($invoice->user_id);

                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->first();

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $invoice->user_id;
                $trans->user_type   = 1;
                $trans->currency_id = 1;
                $trans->amount      = $chargefee->data->fixed_charge;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'wallet_create';
                $trans->details     = trans('Wallet Create');
                $trans->data        = '{"sender":"'.User::findOrFail($invoice->user_id)->name.'", "receiver":"System Account"}';
                $trans->save();

                user_wallet_decrement($invoice->user_id, 1, $chargefee->data->fixed_charge, 1);
                user_wallet_increment(0, 1, $chargefee->data->fixed_charge, 9);
            }

            $rcvWallet->balance += $invoice->get_amount;
            $rcvWallet->update();

            $rcvTrnx              = new Transaction();
            $rcvTrnx->trnx        = $trnx->trnx;
            $rcvTrnx->user_id     = $invoice->user_id;
            $rcvTrnx->user_type   = 1;
            $rcvTrnx->currency_id = $invoice->currency_id;
            $rcvTrnx->wallet_id   = $rcvWallet->id;
            $rcvTrnx->amount      = $invoice->get_amount;
            $rcvTrnx->charge      = $invoice->charge;
            $rcvTrnx->remark      = 'invoice_payment';
            $rcvTrnx->invoice_num = $invoice->number;
            $rcvTrnx->type        = '+';
            $rcvTrnx->details     = trans('Receive Payemnt from invoice : '). $invoice->number;
            $rcvTrnx->data        = '{"sender":"'.auth()->user()->name.'", "receiver":"'.User::findOrFail($invoice->user_id)->name.'"}';
            $rcvTrnx->save();

            $invoice->payment_status = 1;
            $invoice->update();


            @mailSend('received_invoice_payment',[
                'amount' => amount($invoice->get_amount,$invoice->currency->type,2),
                'curr'   => $invoice->currency->code,
                'trnx'   => $rcvTrnx->trnx,
                'from_user' => $invoice->email,
                'inv_num'  => $invoice->number,
                'after_balance' => amount($rcvWallet->balance,$invoice->currency->type,2),
                'charge' => amount($invoice->charge,$invoice->currency->type,2),
                'date_time' => dateFormat($rcvTrnx->created_at)
            ],$invoice->user);

            session()->forget('invoice');
            return redirect(route('user.dashboard'))->with('success','Payment completed');
        }
        else{
            abort(404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function invoic_setting()
    {
        $data['invoice_setting']=InvoiceSetting::where('user_id', auth()->id())->first();
        $data['invoice_type'] = array('0'=>'Invoice', '1'=>'Proforma', '2'=>'Check');
        return view('user.invoice.setting', $data);
    }

    public function invoice_setting_save(Request $request)
    {
        $data = InvoiceSetting::where('user_id', $request->user_id)->first();
        if (!$data) {
            $data = new InvoiceSetting();
        }
        $data->user_id = $request->user_id;
        $data->number_generator = $request->except(array('_token', 'user_id', 'template'));
        $data->template = $request->template;
        $data->save();
        return back()->with('message', 'Invoice Setting has been updated successfully.');
    }

    public function beneficiary_create(Request $request)
    {
        $data = new InvoiceBeneficiary();
        $input = $request->all();
        $data->fill($input)->save();
        return back()->with($data->id);
    }

    public function tax_create(Request $request)
    {
        $data = new Tax();
        $input = $request->all();
        $data->fill($input)->save();
        return $data;
    }
}
