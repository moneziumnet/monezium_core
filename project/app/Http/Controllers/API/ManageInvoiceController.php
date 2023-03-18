<?php

namespace App\Http\Controllers\API;

use Validator;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Charge;
use App\Models\InvItem;
use App\Models\Invoice;
use App\Models\Currency;
use App\Models\Beneficiary;
use App\Models\Tax;
use App\Models\Contract;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\CryptoDeposit;
use App\Models\DepositBank;
use App\Models\BankAccount;
use App\Models\Generalsetting;
use App\Models\InvoiceSetting;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Auth;

class ManageInvoiceController extends Controller
{
    public function index()
    {
        try {
            $data['invoices'] = Invoice::where('user_id',auth()->id())->latest()->paginate(15);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function incoming_index()
    {
        try {
            $user = User::findOrFail(auth()->id());
            $data['invoices'] = Invoice::where('email',$user->email)->latest()->paginate(15);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        try {
            $data['currencies'] = Currency::whereStatus(1)->get();
            if (!isEnabledUserModule('Crypto'))
                $data['currencies'] = Currency::whereStatus(1)->where('type', 1)->get();
            $data['beneficiaries'] = Beneficiary::where('user_id', auth()->id())->get();
            $data['contracts'] = Contract::where('user_id', auth()->id())->where('status', 1)->get();
            $data['products'] = Product::where('user_id', auth()->id())->where('status', 1)->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' =>'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $rules = [
                'currency'   => 'required',
                'item'       => 'required',
                'item.*'     => 'required',
                'amount'     => 'required',
                'amount.*'   => 'required|numeric|gt:0',
                'description' => 'required',
                'beneficiary_id' => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }

            $currency = Currency::findOrFail($request->currency);

            $amount = array_sum($request->amount);
            $beneficiary = Beneficiary::whereId($request->beneficiary_id)->first();
            $setting = InvoiceSetting::where('user_id', auth()->id())->first();
            if(!$setting){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You should confirm the invoice setting first.']);
            }
            $type = 'prefix_'.$request->type;
            $length = 'length_'.$request->type;

            $invoice = new Invoice();
            $invoice->user_id      = auth()->id();
            $invoice->number       = $setting->number_generator->$type.randNum($setting->number_generator->$length);
            $invoice->invoice_to   = $beneficiary->name;
            $invoice->email        = $beneficiary->email;
            $invoice->address      = $beneficiary->registration_no ?? $beneficiary->address;
            $invoice->currency_id  = $currency->id;
            $invoice->charge       = 0;
            $invoice->type         = $request->type;
            $invoice->template     = $request->template;
            $invoice->final_amount = $amount;
            $invoice->get_amount   = $amount;
            $invoice->beneficiary_id = $request->beneficiary_id;
            $invoice->product_id = $request->product_id;
            $invoice->contract_id = $request->contract_id;
            $invoice->contract_aoa_id = $request->aoa_id;
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
                $invItem->tax_id    = $request->tax_id[$i] ?? 0;
                $invItem->save();
                $i++;
            }

            $route = route('invoice.view',encrypt($invoice->number));

            $gs = Generalsetting::first();
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            $headers .= "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $msg = "Hello"." $invoice->invoice_to,<br>".
            "You have pending payment of invoice"." <b>$invoice->number</b>".'<br>Please click the below link to complete your payment<br>'."Invoice details".": <br>"."Amount:".  $amount . $currency->code."<br>Payment Link:"."<a href='$route' target='_blank'>".'Click To Payment'."</a><br>".'Time'." : ". $invoice->created_at;
             sendMail($invoice->email, 'Invoice Payment', $msg, $headers);

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Invoice has been created']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            $data['invoice'] = $invoice;

            $data['currencies'] = Currency::whereStatus(1)->get();
            if (!isEnabledUserModule('Crypto'))
                $data['currencies'] = Currency::whereStatus(1)->where('type', 1)->get();
            $data['beneficiaries'] = Beneficiary::where('user_id', auth()->id())->get();
            $data['contracts'] = Contract::where('user_id', auth()->id())->where('status', 1)->get();
            $data['products'] = Product::where('user_id', auth()->id())->where('status', 1)->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function incoming_edit($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            $data['invoice'] = $invoice;
            $data['currencies'] = Currency::whereStatus(1)->get();
            if (!isEnabledUserModule('Crypto'))
                $data['currencies'] = Currency::whereStatus(1)->where('type', 1)->get();
            $data['beneficiaries'] = Beneficiary::where('user_id', auth()->id())->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

    public function incoming_update(Request $request, $id)
    {
        try {
            $rules = [
                'currency'   => 'required',
                'item'       => 'required',
                'item.*'     => 'required',
                'amount'     => 'required',
                'amount.*'   => 'required|numeric|gt:0',
                'description' => 'required',
                'beneficiary_id' => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }

            $currency = Currency::findOrFail($request->currency);

            $beneficiary = Beneficiary::whereId($request->beneficiary_id)->first();

            $invoice = Invoice::findOrFail($id);
            $invoice->user_id      = auth()->id();
            $invoice->invoice_to   = $beneficiary->name;
            $invoice->email        = $beneficiary->email;
            $invoice->address      = $beneficiary->registration_no ?? $beneficiary->address;
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
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Invoice has been updated']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
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
        try {

            $rules = [
                'currency'   => 'required',
                'item'       => 'required',
                'item.*'     => 'required',
                'amount'     => 'required',
                'amount.*'   => 'required|numeric|gt:0',
                'description' => 'required',
                'beneficiary_id' => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }


            $currency = Currency::findOrFail($request->currency);

            $beneficiary = Beneficiary::whereId($request->beneficiary_id)->first();
            $setting = InvoiceSetting::where('user_id', auth()->id())->first();
            $type = 'prefix_'.$request->type;
            $length = 'length_'.$request->type;

            $invoice = Invoice::findOrFail($id);
            $invoice->user_id      = auth()->id();
            $invoice->invoice_to   = $beneficiary->name;
            $invoice->email        = $beneficiary->email;
            $invoice->number       = $setting->number_generator->$type.randNum($setting->number_generator->$length);
            $invoice->address      = $beneficiary->registration_no ?? $beneficiary->address;
            $invoice->currency_id  = $currency->id;
            $invoice->type         = $request->type;
            $invoice->template     = $request->template;
            $invoice->charge       = 0;
            $invoice->final_amount = array_sum($request->amount);
            $invoice->get_amount   = array_sum($request->amount);
            $invoice->beneficiary_id = $request->beneficiary_id;
            $invoice->description  = $request->description;
            $invoice->product_id   = $request->product_id;
            $invoice->contract_id  = $request->contract_id;
            $invoice->contract_aoa_id = $request->aoa_id;

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
                $invItem->tax_id    = $request->tax_id[$i] ?? 0;
                $invItem->save();
                $i++;
            }
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Invoice has been updated']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function payStatus(Request $request)
    {
        try {
            $invoice = Invoice::findOrFail($request->id);
            if(!$invoice) return response(['error'=>'Invalid request']);

            if($invoice->payment_status == 1){
                $invoice->payment_status = 0;
                $invoice->update();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => ['status' => 'unpaid']]);
            }else{
                $invoice->payment_status = 1;
                $invoice->update();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => ['status' => 'paid']]);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }


    }

    public function publishStatus(Request $request)
    {
        try {
            $invoice = Invoice::findOrFail($request->id);
            if(!$invoice) return response(['error'=>'Invalid request']);

            if($invoice->status == 1){
                $invoice->status = 0;
                $invoice->update();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => ['status' => 'unpublish']]);
            }else{
                $invoice->status = 1;
                $invoice->update();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => ['status' => 'publish']]);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

    public function cancel($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            $invoice->status = 2;
            $invoice->save();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Invoice has been cancelled']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function view($number)
    {
        try {
            $data['invoice'] = Invoice::where('number',$number)->firstOrFail();
            $data['user'] = User::where('id',$data['invoice']->user_id)->first();
            $data['back_url'] = route('user.invoice.index');
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function incoming_view($number)
    {
        try {
            $data['invoice'] = Invoice::where('number',$number)->firstOrFail();
            $data['user'] = User::where('id',$data['invoice']->user_id)->first();
            $data['back_url'] = route('user.invoice.incoming.index');
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

    public function sendToMail(Request $request)
    {
        try {
            $invoice = Invoice::findOrFail($request->invoice_id);
            $currency = $invoice->currency;
            $amount = $invoice->final_amount;
            $route = route('invoice.view',encrypt($invoice->number));

            $msg = "Hello"." $invoice->invoice_to,<br>"."You have pending payment of invoice"." <b>$invoice->number</b>."."Please click the below link to complete your payment" .".<br>"."Invoice details".": <br>"."Amount"  .":  $amount $currency->code <br>"."Payment Link"." :  <a href='$route' target='_blank'>"."Click To Payment"."</a><br>"."QR Code"." :  <img src='".generateQR($route)."' class='' alt=''><br>"."Time"." : $invoice->created_at";

            $gs = Generalsetting::first();
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            $headers .= "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            sendMail($request->email, 'Invoice Payment', $msg, $headers);

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Invoice has been sent to the recipient']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function invoicePayment($number)
    {
        try {
                $invoice = Invoice::where('number',decrypt($number))->firstOrFail();
                $user = User::where('id',$invoice->user_id)->firstOrFail();
                $bankaccounts = BankAccount::where('user_id', $user->id)->where('currency_id', $invoice->currency_id)->get();

                $crypto_ids =  Wallet::where('user_id', $invoice->user_id)->where('user_type',1)->where('wallet_type', 8)->pluck('currency_id')->toArray();
                $cryptolist = Currency::whereStatus(1)->where('type', 2)->whereIn('id', $crypto_ids)->get();

                $inv_items = InvItem::where('invoice_id', $invoice->id)->get();
                $tax_value = 0;
                foreach ($inv_items as $value) {
                    $tax_value += $value->tax->rate * $value->amount / 100;
                }
                if($invoice->payment_status == 1){
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Invoice already been paid']);
                }
            if($invoice->user_id == auth()->id()){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You can not pay your own invoice.']);
            }

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => compact('invoice', 'tax_value','bankaccounts', 'cryptolist')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }


    public function invoicePaymentSubmit(Request $request)
    {
        try {
            $invoice = Invoice::findOrFail($request->invoice_id);
            $gs = Generalsetting::first();
            if (auth()->user() && auth()->id() == $invoice->user_id) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You can not pay yourself.']);
            }
            if($request->payment == 'gateway'){
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Gateway Payment completed']);
            } else if($request->payment == 'bank_pay'){

                $bankaccount = BankAccount::where('id', $request->bank_account)->first();
                $invoice = Invoice::findOrFail($request->invoice_id);

                $deposit = new DepositBank();
                $deposit['deposit_number'] = $request->deposit_no;
                $deposit['user_id'] = $invoice->user_id;
                $deposit['currency_id'] = $invoice->currency_id;
                $deposit['amount'] = $invoice->final_amount;
                $deposit['sub_bank_id'] = $bankaccount->subbank_id;
                $deposit['status'] = "pending";
                $deposit->save();
                $currency = Currency::where('id',$invoice->currency_id)->first();
                $subbank = SubInsBank::findOrFail($bankaccount->subbank_id);
                $user = User::findOrFail($bankaccount->user_id);
                mailSend('deposit_request',['amount'=>$deposit->amount, 'curr' => ($currency ? $currency->code : ' '), 'date_time'=>$deposit->created_at ,'type' => 'Bank', 'method'=> $subbank->name ], $user);
                send_notification($invoice->user_id, 'Bank has been deposited by '.(auth()->user()->company_name ?? auth()->user()->name).'. Please check.', route('admin.deposits.bank.index'));

                send_whatsapp($invoice->user_id, 'Bank has been deposited by '.(auth()->user()->company_name ?? auth()->user()->name)."\n Amount is ".$currency->symbol.$invoice->final_amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                send_telegram($invoice->user_id, 'Bank has been deposited by '.(auth()->user()->company_name ?? auth()->user()->name)."\n Amount is ".$currency->symbol.$invoice->final_amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                send_staff_telegram('Bank has been deposited by '.(auth()->user()->company_name ?? auth()->user()->name)."\n Amount is ".$currency->symbol.$invoice->final_amount."\n Transaction ID : ".$request->deposit_no."\nPlease check more details to click this url\n".route('admin.deposits.bank.index'), 'Deposit Bank');

                $invoice->payment_status = 1;
                $invoice->update();

                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Bank Payment completed']);
            } else if($request->payment == 'crypto'){
                $data = new CryptoDeposit();
                $data->currency_id = $request->currency_id;
                $data->amount = $request->amount;
                $invoice = Invoice::findOrFail($request->id);
                $data->user_id = $invoice->user_id;
                $data->address = $request->address;
                $data->save();
                $invoice->payment_status = 1;
                $invoice->update();

                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Crypto Payment completed']);
            } else if($request->payment == 'wallet'){
                try {
                    $invoice = decrypt(session('invoice'));
                } catch (\Throwable $th) {
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
                }

                $wallet = Wallet::where('user_id',auth()->id())->where('user_type',1)->where('currency_id',$invoice->currency_id)->where('wallet_type', 1)->first();

                if(!$wallet){
                    $wallet =  Wallet::create([
                        'user_id'     => auth()->id(),
                        'user_type'   => 1,
                        'currency_id' => $invoice->currency_id,
                        'balance'     => 0,
                        'wallet_type' => 1,
                        'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
                    ]);

                    $user = User::findOrFail(auth()->id());

                    $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                    if(!$chargefee) {
                        $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                    }

                    $trans = new Transaction();
                    $trans->trnx = str_rand();
                    $trans->user_id     = $user->id;
                    $trans->user_type   = 1;
                    $trans->currency_id = defaultCurr();
                    $trans->amount      = 0;
                    $trans_wallet = get_wallet($user->id, defaultCurr(), 1);
                    $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                    $trans->charge      = $chargefee->data->fixed_charge;
                    $trans->type        = '-';
                    $trans->remark      = 'account-open';
                    $trans->details     = trans('Wallet Create');
                    $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
                    $trans->save();

                    $currency = Currency::findOrFail(defaultCurr());

                    mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'type' => 'Current', 'date_time'=> dateFormat($trans->created_at)], $user);


                    user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
                    user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
                }

                if($wallet->balance < $invoice->final_amount) {
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Insufficient balance to your wallet']);
                }
                $inv_items = InvItem::where('invoice_id', $invoice->id)->get();
                $tax_value = 0;
                foreach ($inv_items as $value) {
                    $tax_value += $value->tax->rate * $value->amount / 100;
                }
                user_wallet_increment(0, $invoice->currency_id, $tax_value, 9);

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = auth()->id();
                $trans->user_type   = 1;
                $trans->currency_id = $invoice->currency_id;
                $trans->amount      = 0;
                $trans->charge      = $tax_value;
                $trans->wallet_id   = $wallet->id;
                $trans->type        = '-';
                $trans->remark      = 'invoice_tax_fee';
                $trans->details     = trans('Invoice Tax Fee');
                $trans->invoice_num = $invoice->number;
                $trans->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.$gs->disqus.'", "description": "'.$invoice->description.'"}';
                $trans->save();

                $wallet->balance -= $invoice->final_amount;
                $wallet->balance -= $tax_value;
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
                $trnx->details     = trans('Payment to invoice : '). $invoice->number;
                $trnx->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.(User::findOrFail($invoice->user_id)->company_name ?? User::findOrFail($invoice->user_id)->name).'", "description": "'.$invoice->description.'"}';
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

                    $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                    if(!$chargefee) {
                        $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                    }

                    $trans = new Transaction();
                    $trans->trnx = str_rand();
                    $trans->user_id     = $invoice->user_id;
                    $trans->user_type   = 1;
                    $trans->currency_id = defaultCurr();
                    $trans_wallet = get_wallet($invoice->user_id, defaultCurr(), 1);
                    $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                    $trans->amount      = 0;
                    $trans->charge      = $chargefee->data->fixed_charge;
                    $trans->type        = '-';
                    $trans->remark      = 'account-open';
                    $trans->details     = trans('Wallet Create');
                    $trans->data        = '{"sender":"'.(User::findOrFail($invoice->user_id)->company_name ?? User::findOrFail($invoice->user_id)->name).'", "receiver":"'.$gs->disqus.'"}';
                    $trans->save();

                    $currency = Currency::findOrFail(defaultCurr());

                    mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'type' => 'Current', 'date_time'=> dateFormat($trans->created_at)], $user);

                    user_wallet_decrement($invoice->user_id, defaultCurr(), $chargefee->data->fixed_charge, 1);
                    user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
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
                $rcvTrnx->details     = trans('Receive Payment from invoice : '). $invoice->number;
                $rcvTrnx->data        = '{"sender":"'.(auth()->user()->company_name ?? auth()->user()->name).'", "receiver":"'.(User::findOrFail($invoice->user_id)->company_name ?? User::findOrFail($invoice->user_id)->name).'", "description": "'.$invoice->description.'"}';
                $rcvTrnx->save();

                $invoice->payment_status = 1;
                $invoice->update();


                mailSend('received_invoice_payment',[
                    'amount' => amount($invoice->get_amount,$invoice->currency->type,2),
                    'curr'   => $invoice->currency->code,
                    'trnx'   => $rcvTrnx->trnx,
                    'from_user' => $invoice->email,
                    'inv_num'  => $invoice->number,
                    'after_balance' => amount($rcvWallet->balance,$invoice->currency->type,2),
                    'charge' => amount($invoice->charge,$invoice->currency->type,2),
                    'date_time' => dateFormat($rcvTrnx->created_at)
                ],$invoice->user);

                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Payment completed']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

    public function invoicePaymentCrypto($id, Request $request)
    {
        try {
            $data['invoice'] = Invoice::where('id', $id)->first();

            $inv_items = InvItem::where('invoice_id', $id)->get();
            $tax_value = 0;
            foreach ($inv_items as $value) {
                $tax_value += $value->tax->rate * $value->amount / 100;
            }

            $data['total_amount'] = $data['invoice']->final_amount + $tax_value;
            $pre_currency = Currency::findOrFail($data['invoice']->currency_id);
            $select_currency = Currency::findOrFail($request->link_pay_submit);
            $code = $select_currency->code;
            $data['cal_amount'] = floatval(getRate($pre_currency, $code));
            $data['wallet'] =  Wallet::where('user_id', $data['invoice']->user_id)->where('user_type',1)->where('wallet_type', 8)->where('currency_id', $select_currency->id)->first();
            if(!$data['wallet']) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $select_currency->code .' Crypto wallet does not existed in sender.']);
            }
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);

        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }



    public function invoic_setting()
    {
        try {
            $data['invoice_setting']=InvoiceSetting::where('user_id', auth()->id())->first();
            $data['invoice_type'] = array('0'=>'Invoice', '1'=>'Proforma', '2'=>'Check');
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function invoice_setting_save(Request $request)
    {
        try {
            $data = InvoiceSetting::where('user_id', $request->user_id)->first();
            if (!$data) {
                $data = new InvoiceSetting();
            }
            $data->user_id = $request->user_id;
            $data->number_generator = $request->except(array('_token', 'user_id', 'template'));
            $data->template = $request->template;
            $data->save();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Invoice Setting has been updated successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function tax_create(Request $request)
    {
        try {
            $data = new Tax();
            $input = $request->all();
            $data->fill($input)->save();
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'success', 'data' => ['tax' => $data]]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }
}
