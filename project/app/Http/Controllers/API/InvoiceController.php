<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\UserApiCred;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\Currency;
use App\Models\Charge;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Classes\GoogleAuthenticator;
use PHPMailer\PHPMailer\PHPMailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class InvoiceController extends Controller
{
    public $successStatus = 200;

     /********** Start Invoice API******/
     public function invoices(Request $request)
     {
         try{
             $user_id = Auth::user()->id;
             $data['invoices'] = Invoice::with('currency')->whereUserId($user_id)->orderby('id','desc')->paginate(10);
             return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
         }catch(\Throwable $th)
         {
             return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
         }
     }
 
     public function invoiceview(Request $request)
     {
         try{
             $user_id = Auth::user()->id;
             $rules = [
                'invoice_number' => 'required'
            ];
             
             $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
 
             $number = $request->invoice_number;
             //$invoice = Invoice::where('number',decrypt($number))->firstOrFail();
             $data['invoices'] = Invoice::with('currency')->whereUserId($user_id)->where('number',$number)->firstOrFail();
             return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
         }catch(\Throwable $th){
             return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
         }
     }
 
 
     public function createinvoice(Request $request)
     {
         try{
            $user_id = Auth::user()->id;
            $rules = [
                'invoice_to' => 'required',
                'email'      => 'required|email',
                'address'    => 'required',
                'currency_id'   => 'required',
                'item'       => 'required',
                'item.*'     => 'required',
                'amount'     => 'required',
                'amount.*'   => 'required|numeric|gt:0'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }

            
             $charge = charge('create-invoice');
             $currency = Currency::findOrFail($request->currency_id);
 
             $amount = array_sum($request->amount);
             $finalCharge = chargeCalc($charge,$amount,getRate($currency));
             $willGetAmount = numFormat($amount - $finalCharge);
 
             $invoice = new Invoice();
             $invoice->user_id      = $user_id;
             $invoice->number       = 'INV-'.randNum(8);
             $invoice->invoice_to   = $request->invoice_to;
             $invoice->email        = $request->email;
             $invoice->address      = $request->address;
             $invoice->currency_id  = $currency->id;
             $invoice->charge       = $finalCharge;
             $invoice->final_amount = $amount;
             $invoice->get_amount   = $willGetAmount;
             $invoice->save();
 
             $items = array_combine($request->item,$request->amount);
             foreach($items as $item => $amount){
                 $invItem             = new InvItem();
                 $invItem->invoice_id = $invoice->id;
                 $invItem->name       = $item;
                 $invItem->amount	 = $amount;
                 $invItem->save();
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
             $data['invoices'] = Invoice::with('currency')->whereUserId($user_id)->where('number',$invoice->number)->firstOrFail();
             return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Invoice has been created', 'data' => $data]);
         }catch(\Throwable $th){
             return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
         }
     }
 
     public function invoiceurl(Request $request)
     {
         try{
             $user_id = Auth::user()->id;
 
             $rules = [
                'invoice_number' => 'required'
            ];
             
             $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
             $number = $request->invoice_number;
 
             $route = route('invoice.view',encrypt($number));
             $data['invoice_link'] = $route;
             return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
         }catch(\Throwable $th){
             return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Something invalid.']);
         }
     }
     /********** End Invoice API******/
}
