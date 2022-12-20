<?php
namespace App\Handler;

use App\Models\Currency;
use App\Models\WebhookRequest;
use App\Models\DepositBank;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookResponse\RespondsToWebhook;
use Symfony\Component\HttpFoundation\Response;

class OpenpaydResponse implements RespondsToWebhook
{
    public function respondToValidWebhook(Request $request, WebhookConfig $config): Response
    {
        $obj = json_decode($request->getContent());

        if($obj->type == 'PAYIN'){
            if(!$obj->transactionReference)
                return response()->json("failure");
            $currency = Currency::where('code', $obj->amount->currency)->first();
            $webrequest = WebhookRequest::where('reference', $obj->transactionReference)
                ->where('gateway_type', 'openpayd')
                ->first();
            if(!$webrequest)
                $webrequest = new WebhookRequest();
            
            $webrequest->transaction_id = $obj->transactionId;
            $webrequest->sender_name = $obj->senderName;
            $webrequest->sender_address = $obj->senderAddress;
            $webrequest->amount = $obj->amount->value;
            $webrequest->currency_id = $currency ? $currency->id : 0;
            $webrequest->status = strtolower($obj->status);
            $webrequest->reference = $obj->transactionReference;
            $webrequest->failure_reason = $obj->failureReason??"";
            $webrequest->gateway_type = "openpayd";
            $webrequest->is_pay_in = true;

            $webrequest->save();

            $deposit = DepositBank::whereRaw("INSTR('".$obj->transactionReference."', deposit_number) > 0")->first();
            if(!$deposit) {
                $new_deposit = new DepositBank();
                $user = User::where('holder_id', $obj->accountHolderId)->first();

                if(!$user)
                    return response()->json("failure");

                $new_deposit['deposit_number'] = $obj->transactionReference;
                $new_deposit['user_id'] = $user->id;
                $new_deposit['currency_id'] = $webrequest->currency_id;
                $new_deposit['amount'] = $obj->amount->value;
                $new_deposit['status'] = "pending";
                $new_deposit['sub_bank_id'] = null;
                $new_deposit->save();
            }

            return response()->json("success");
        }
        if($obj->type == 'PAYOUT'){
            $webrequest = WebhookRequest::where('transaction_id', $obj->transactionId)
                ->where('gateway_type', 'openpayd')
                ->where('is_pay_in', false)
                ->first();
            if(!$webrequest)
                $webrequest = new WebhookRequest();
            
            $webrequest->transaction_id = $obj->transactionId;
            $webrequest->status = strtolower($obj->status);
            $webrequest->gateway_type = "openpayd";
            $webrequest->is_pay_in = true;

            $webrequest->save();
            return response()->json("success");
        }
        return response()->json("failure");
    }
}
