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
            $webrequest = WebhookRequest::where('transaction_id', $obj->transactionId)
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
            $webrequest->reference = $obj->transactionReference ?? $obj->transactionId;
            $webrequest->failure_reason = $obj->failureReason??"";
            $webrequest->gateway_type = "openpayd";
            $webrequest->is_pay_in = true;

            $webrequest->save();


            if ($obj->transactionReference == null) {
                $deposit = DepositBank::whereRaw("INSTR('".$obj->transactionId."', deposit_number) > 0")->first();
                if(!$deposit) {
                    $new_deposit = new DepositBank();
                    $user = User::where('holder_id', $obj->accountHolderId)->first();

                    if(!$user)
                        return response()->json("failure");

                    $new_deposit['deposit_number'] = $obj->transactionId;
                    $new_deposit['user_id'] = $user->id;
                    $new_deposit['currency_id'] = $webrequest->currency_id;
                    $new_deposit['amount'] = $obj->amount->value;
                    $new_deposit['status'] = "pending";
                    $new_deposit['details'] = $obj->transactionReference;
                    $new_deposit['sub_bank_id'] = null;
                    $new_deposit->save();
                    send_notification($user->id, 'Bank has been deposited by '.$obj->senderName.'. Please check.', route('admin.deposits.bank.index'));
                    send_whatsapp($user->id, 'Bank has been deposited by '.$obj->senderName."\n Amount is ".$currency->symbol.$obj->amount->value."\n Payment Gateway : Openpayd"."\n Transaction ID : ".$obj->transactionId."\nPlease check more details to click this url\n".route('user.depositbank.index'));

                }
            }
            else {
                $deposit = DepositBank::whereRaw("INSTR('".$obj->transactionReference."', deposit_number) > 0")->first();
                if(!$deposit) {
                    $new_deposit = new DepositBank();
                    $user = User::where('holder_id', $obj->accountHolderId)->first();

                    if(!$user)
                        return response()->json("failure");

                    $new_deposit['deposit_number'] = $obj->transactionId;
                    $new_deposit['user_id'] = $user->id;
                    $new_deposit['currency_id'] = $webrequest->currency_id;
                    $new_deposit['amount'] = $obj->amount->value;
                    $new_deposit['status'] = "pending";
                    $new_deposit['details'] = $obj->transactionReference;
                    $new_deposit['sub_bank_id'] = null;
                    $new_deposit->save();
                    send_notification($user->id, 'Bank has been deposited by '.$obj->senderName.'. Please check.', route('admin.deposits.bank.index'));
                    send_whatsapp($user->id, 'Bank has been deposited by '.$obj->senderName."\n Amount is ".$currency->symbol.$obj->amount->value."\n Payment Gateway : Openpayd"."\n Transaction ID : ".$obj->transactionId."\nPlease check more details to click this url\n".route('user.depositbank.index'));

                }
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
