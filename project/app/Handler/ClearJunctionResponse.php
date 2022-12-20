<?php

namespace App\Handler;

use DateTime;
use GuzzleHttp\Client;
use App\Models\Currency;
use App\Models\BankAccount;
use App\Models\BankGateway;
use App\Models\DepositBank;
use Illuminate\Http\Request;
use App\Models\WebhookRequest;
use App\Http\Controllers\Controller;

class ClearJunctionResponse extends Controller
{
    public function payin(Request $request)
    {
        $obj = str2obj($request->getContent());
        
        $currency = Currency::where('code', $obj->currency)->first();
        $webrequest = WebhookRequest::where('reference', $obj->label)
            ->where('gateway_type', 'clearjunction')
            ->where('is_pay_in', true)
            ->first();
        if(!$webrequest)
            $webrequest = new WebhookRequest();
        
        $webrequest->transaction_id = $obj->clientOrder;
        $webrequest->sender_name = $obj->paymentDetails->payerRequisite->name;
        $webrequest->sender_address = "";
        $webrequest->amount = $obj->amount;
        $webrequest->currency_id = $currency ? $currency->id : 0;
        
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
        $webrequest->reference = $obj->label;
        $webrequest->gateway_type = "clearjunction";
        $webrequest->is_pay_in = true;
        $webrequest->save();

        $deposit = DepositBank::whereRaw("INSTR('".$obj->label."', deposit_number) > 0")->first();
        if(!$deposit) {
            $new_deposit = new DepositBank();
            $bankaccount = BankAccount::where('iban', $obj->paymentDetails->payeeRequisite->iban)->first();

            if(!$bankaccount)
                return response()->json("failure");

            $new_deposit['deposit_number'] = $obj->label;
            $new_deposit['user_id'] = $bankaccount->user_id;
            $new_deposit['currency_id'] = $webrequest->currency_id;
            $new_deposit['amount'] = $obj->amount;
            $new_deposit['status'] = "pending";
            $new_deposit['sub_bank_id'] = $bankaccount->subbank_id;
            $new_deposit->save();
        }

        return response()->json("success");
    }

    public function payout(Request $request) {
        $obj = str2obj($request->getContent());
        
        $webrequest = WebhookRequest::where('transaction_id', $obj->orderReference)
            ->where('gateway_type', 'clearjunction')
            ->where('is_pay_in', false)
            ->first();
        if(!$webrequest)
            $webrequest = new WebhookRequest();
        
        $webrequest->transaction_id = $obj->orderReference;
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
        return response()->json("success");
    }
}