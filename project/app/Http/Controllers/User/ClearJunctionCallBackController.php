<?php

namespace App\Http\Controllers\User;

use DateTime;
use GuzzleHttp\Client;
use App\Models\Currency;
use App\Models\BankAccount;
use App\Models\BankGateway;
use App\Models\DepositBank;
use App\Models\SubInsBank;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\WebhookRequest;
use App\Http\Controllers\Controller;
use \Spatie\WebhookClient\Models\WebhookCall;

class ClearJunctionCallBackController extends Controller
{
    public function payin(Request $request)
    {
        $obj = str2obj($request->getContent());
        $clearjunction = new WebhookCall();
        $clearjunction->name = 'clearjunction';
        $clearjunction->payload =str2obj($request->getContent());
        $clearjunction->url = route('cj-payin');
        $clearjunction->save();


        $currency = Currency::where('code', $obj->currency)->first();
        $webrequest = WebhookRequest::where('transaction_id', $obj->orderReference)
            ->where('gateway_type', 'clearjunction')
            ->where('is_pay_in', true)
            ->first();
        if(!$webrequest)
            $webrequest = new WebhookRequest();

        $webrequest->transaction_id = $obj->orderReference;
        $webrequest->sender_name = $obj->paymentDetails->payerRequisite->name;
        $webrequest->sender_address = "";
        $webrequest->data = $obj;
        $webrequest->reference = $obj->paymentDetails->description ?? $obj->orderReference;
        $webrequest->amount = $obj->amount;
        $webrequest->charge = abs($obj->operationAmount - $obj->amount);
        $webrequest->currency_id = $currency ? $currency->id : 0;

        switch($obj->status) {
            case "created":
            case "captured":
            case "pending":
                $webrequest->status = "processing";
                break;
            case "settled":
                $webrequest->status = "completed";
                break;
            default:
                $webrequest->status = "failed";
                break;
        }
        $webrequest->gateway_type = "clearjunction";
        $webrequest->is_pay_in = true;
        $webrequest->save();
        if ($obj->paymentDetails->description == null) {
            $deposit = DepositBank::Where('deposit_number', $obj->orderReference)->first();
            if(!$deposit) {
                $new_deposit = new DepositBank();
                $bankaccount = BankAccount::where('iban', $obj->paymentDetails->payeeRequisite->iban)->first();

                if(!$bankaccount)
                    return response()->json($obj->orderReference);

                $new_deposit['deposit_number'] = $obj->orderReference;
                $new_deposit['user_id'] = $bankaccount->user_id;
                $new_deposit['currency_id'] = $webrequest->currency_id;
                $new_deposit['amount'] = $obj->amount;
                $new_deposit['details'] = $obj->paymentDetails->description;
                $new_deposit['status'] = "pending";
                $new_deposit['sub_bank_id'] = $bankaccount->subbank_id;
                $new_deposit->save();
                $subbank = SubInsBank::findOrFail($bankaccount->subbank_id);
                $user = User::findOrFail($bankaccount->user_id);
                mailSend('deposit_request',['amount'=>$new_deposit->amount, 'curr' => ($currency ? $currency->code : ' '), 'date_time'=>$new_deposit->created_at ,'type' => 'Bank', 'method'=> $subbank->name ], $user);
                send_notification($bankaccount->user_id, 'Bank has been deposited by '.$obj->paymentDetails->payerRequisite->name."\n Amount is ".$currency->symbol.$obj->amount."\n Payment Gateway : ClearJunction"."\n Transaction ID : ".$obj->orderReference, route('admin.deposits.bank.index'));
                send_whatsapp($bankaccount->user_id, 'Bank has been deposited by '.$obj->paymentDetails->payerRequisite->name."\n Amount is ".$currency->symbol.$obj->amount."\n Payment Gateway : ClearJunction"."\n Transaction ID : ".$obj->orderReference."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                send_telegram($bankaccount->user_id, 'Bank has been deposited by '.$obj->paymentDetails->payerRequisite->name."\n Amount is ".$currency->symbol.$obj->amount."\n Payment Gateway : ClearJunction"."\n Transaction ID : ".$obj->orderReference."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                send_staff_telegram('Bank has been deposited by '.$obj->paymentDetails->payerRequisite->name."\n Amount is ".$currency->symbol.$obj->amount."\n Payment Gateway : ClearJunction"."\n Transaction ID : ".$obj->orderReference."\nPlease check more details to click this url\n".route('admin.deposits.bank.index'), 'Deposit Bank');


            }
        }
        else {
            $deposit = DepositBank::whereRaw("INSTR('".$obj->paymentDetails->description."', deposit_number) > 0")->orWhereRaw("INSTR('".$obj->orderReference."', deposit_number) > 0")->first();
            if(!$deposit) {
                $new_deposit = new DepositBank();
                $bankaccount = BankAccount::where('iban', $obj->paymentDetails->payeeRequisite->iban)->first();

                if(!$bankaccount)
                    return response()->json($obj->orderReference);

                $new_deposit['deposit_number'] = $obj->orderReference;
                $new_deposit['user_id'] = $bankaccount->user_id;
                $new_deposit['currency_id'] = $webrequest->currency_id;
                $new_deposit['amount'] = $obj->amount;
                $new_deposit['details'] = $obj->paymentDetails->description;
                $new_deposit['status'] = "pending";
                $new_deposit['sub_bank_id'] = $bankaccount->subbank_id;
                $new_deposit->save();
                $subbank = SubInsBank::findOrFail($bankaccount->subbank_id);
                $user = User::findOrFail($bankaccount->user_id);
                mailSend('deposit_request',['amount'=>$new_deposit->amount, 'curr' => ($currency ? $currency->code : ' '), 'date_time'=>$new_deposit->created_at ,'type' => 'Bank', 'method'=> $subbank->name ], $user);
                send_notification($bankaccount->user_id, 'Bank has been deposited by '.$obj->paymentDetails->payerRequisite->name."\n Amount is ".$currency->symbol.$obj->amount."\n Payment Gateway : ClearJunction"."\n Transaction ID : ".$obj->orderReference, route('admin.deposits.bank.index'));
                send_whatsapp($bankaccount->user_id, 'Bank has been deposited by '.$obj->paymentDetails->payerRequisite->name."\n Amount is ".$currency->symbol.$obj->amount."\n Payment Gateway : ClearJunction"."\n Transaction ID : ".$obj->orderReference."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                send_telegram($bankaccount->user_id, 'Bank has been deposited by '.$obj->paymentDetails->payerRequisite->name."\n Amount is ".$currency->symbol.$obj->amount."\n Payment Gateway : ClearJunction"."\n Transaction ID : ".$obj->orderReference."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                send_staff_telegram('Bank has been deposited by '.$obj->paymentDetails->payerRequisite->name."\n Amount is ".$currency->symbol.$obj->amount."\n Payment Gateway : ClearJunction"."\n Transaction ID : ".$obj->orderReference."\nPlease check more details to click this url\n".route('admin.deposits.bank.index'), 'Deposit Bank');
            }

        }

        return response()->json($obj->orderReference);
    }

    public function payout(Request $request) {
        $obj = str2obj($request->getContent());
        $clearjunction = new WebhookCall();
        $clearjunction->name = 'clearjunction';
        $clearjunction->payload =str2obj($request->getContent());
        $clearjunction->url = route('cj-payout');
        $clearjunction->save();

        $webrequest = WebhookRequest::where('transaction_id', $obj->orderReference)
            ->where('gateway_type', 'clearjunction')
            ->where('is_pay_in', false)
            ->first();
        if(!$webrequest)
            $webrequest = new WebhookRequest();

        $webrequest->transaction_id = $obj->orderReference;
        $webrequest->charge = abs($obj->operationAmount - $obj->amount);
        $webrequest->data = $obj;
        $webrequest->is_pay_in = false;

        switch($obj->status) {
            case "created":
            case "pending":
                $webrequest->status = "processing";
                break;
            case "settled":
                $webrequest->status = "completed";
                break;
            default:
                $webrequest->status = "failed";
                break;
        }
        $webrequest->gateway_type = "clearjunction";
        $webrequest->save();
        return response()->json($obj->orderReference);
    }
}
