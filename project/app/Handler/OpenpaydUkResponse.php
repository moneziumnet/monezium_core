<?php
namespace App\Handler;

use App\Models\Currency;
use App\Models\WebhookRequest;
use App\Models\DepositBank;
use App\Models\BankGateway;
use App\Models\User;
use App\Models\SubInsBank;
use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookResponse\RespondsToWebhook;
use Symfony\Component\HttpFoundation\Response;

class OpenpaydUkResponse implements RespondsToWebhook
{
    public function respondToValidWebhook(Request $request, WebhookConfig $config): Response
    {
        $obj = json_decode($request->getContent());

        if($obj->type == 'PAYIN'){
            if(!$obj->transactionReference)
                return response()->json("failure");
            $currency = Currency::where('code', $obj->amount->currency)->first();
            $webrequest = WebhookRequest::where('transaction_id', $obj->transactionId)
                ->where('gateway_type', 'openpayd-uk')
                ->first();
            if(!$webrequest)
                $webrequest = new WebhookRequest();

            $webrequest->transaction_id = $obj->transactionId;
            $webrequest->sender_name = $obj->senderName;
            $webrequest->sender_address = $obj->senderAddress;
            $webrequest->amount = $obj->amount->value;
            $webrequest->data = $obj;
            $webrequest->currency_id = $currency ? $currency->id : 0;
            $webrequest->status = strtolower($obj->status);
            $webrequest->reference = $obj->transactionReference ?? $obj->transactionId;
            $webrequest->failure_reason = $obj->failureReason??"";
            $webrequest->gateway_type = "openpayd-uk";
            $webrequest->is_pay_in = true;

            $webrequest->save();


            if ($obj->transactionReference == null) {
                $deposit = DepositBank::whereRaw("INSTR('".$obj->transactionId."', deposit_number) > 0")->first();
                if(!$deposit) {
                    $new_deposit = new DepositBank();
                    $user = User::where('holder_id', $obj->accountHolderId)->first();
                    $subbank = BankGateway::where('keyword', 'openpayd')->with('subinsbank')->get();
                    foreach ($subbank as $key => $value) {
                       if($value->subinsbank->status == 1) {
                        $subbank_id = $value->subinsbank->id;
                       }
                    }

                    if(!$user)
                        return response()->json("failure");

                    $new_deposit['deposit_number'] = $obj->transactionId;
                    $new_deposit['user_id'] = $user->id;
                    $new_deposit['currency_id'] = $webrequest->currency_id;
                    $new_deposit['amount'] = $obj->amount->value;
                    $new_deposit['status'] = "pending";
                    $new_deposit['details'] = $obj->transactionReference;
                    $new_deposit['sub_bank_id'] = isset($subbank_id) ? $subbank_id : null;
                    $new_deposit->save();
                    send_notification($user->id, 'Bank has been deposited by '.$obj->senderName."\n Amount is ".$currency->symbol.$obj->amount->value."\n Payment Gateway : Openpayd UK"."\n Transaction ID : ".$obj->transactionId, route('admin.deposits.bank.index'));
                    send_whatsapp($user->id, 'Bank has been deposited by '.$obj->senderName."\n Amount is ".$currency->symbol.$obj->amount->value."\n Payment Gateway : Openpayd UK"."\n Transaction ID : ".$obj->transactionId."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                    send_telegram($user->id, 'Bank has been deposited by '.$obj->senderName."\n Amount is ".$currency->symbol.$obj->amount->value."\n Payment Gateway : Openpayd Uk"."\n Transaction ID : ".$obj->transactionId."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                    send_staff_telegram('Bank has been deposited by '.$obj->senderName."\n Amount is ".$currency->symbol.$obj->amount->value."\n Payment Gateway : Openpayd Uk"."\n Transaction ID : ".$obj->transactionId."\nPlease check more details to click this url\n".route('admin.deposits.bank.index'), 'Deposit Bank');
                    if(isset($subbank_id)){
                        $method =  SubInsBank::findOrFail($subbank_id)->name;
                    }
                    else {
                        $method = 'OpenPayd Uk';
                    }
                    mailSend('deposit_request',['amount'=>$new_deposit->amount, 'curr' => ($currency ? $currency->code : ' '), 'date_time'=>$new_deposit->created_at ,'type' => 'Bank', 'method'=> $method], $user);


                }
            }
            else {
                $deposit = DepositBank::whereRaw("INSTR('".$obj->transactionReference."', deposit_number) > 0")->orWhereRaw("INSTR('".$obj->transactionId."', deposit_number) > 0")->first();
                if(!$deposit) {
                    $new_deposit = new DepositBank();
                    $user = User::where('holder_id', $obj->accountHolderId)->first();
                    $subbank = BankGateway::where('keyword', 'openpayd-uk')->with('subinsbank')->get();
                    foreach ($subbank as $key => $value) {
                       if($value->subinsbank->status == 1) {
                        $subbank_id = $value->subinsbank->id;
                       }
                    }

                    if(!$user)
                        return response()->json("failure");

                    $new_deposit['deposit_number'] = $obj->transactionId;
                    $new_deposit['user_id'] = $user->id;
                    $new_deposit['currency_id'] = $webrequest->currency_id;
                    $new_deposit['amount'] = $obj->amount->value;
                    $new_deposit['status'] = "pending";
                    $new_deposit['details'] = $obj->transactionReference;
                    $new_deposit['sub_bank_id'] = isset($subbank_id) ? $subbank_id : null;
                    $new_deposit->save();
                    if(isset($subbank_id)){
                        $method =  SubInsBank::findOrFail($subbank_id)->name;
                    }
                    else {
                        $method = 'OpenPayd Uk';
                    }
                    mailSend('deposit_request',['amount'=>$new_deposit->amount, 'curr' => ($currency ? $currency->code : ' '), 'date_time'=>$new_deposit->created_at ,'type' => 'Bank', 'method'=> $method ], $user);
                    send_notification($user->id, 'Bank has been deposited by '.$obj->senderName."\n Amount is ".$currency->symbol.$obj->amount->value."\n Payment Gateway : Openpayd Uk"."\n Transaction ID : ".$obj->transactionId, route('admin.deposits.bank.index'));
                    send_whatsapp($user->id, 'Bank has been deposited by '.$obj->senderName."\n Amount is ".$currency->symbol.$obj->amount->value."\n Payment Gateway : Openpayd Uk"."\n Transaction ID : ".$obj->transactionId."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                    send_telegram($user->id, 'Bank has been deposited by '.$obj->senderName."\n Amount is ".$currency->symbol.$obj->amount->value."\n Payment Gateway : Openpayd Uk"."\n Transaction ID : ".$obj->transactionId."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                    send_staff_telegram('Bank has been deposited by '.$obj->senderName."\n Amount is ".$currency->symbol.$obj->amount->value."\n Payment Gateway : Openpayd Uk"."\n Transaction ID : ".$obj->transactionId."\nPlease check more details to click this url\n".route('admin.deposits.bank.index'), 'Deposit Bank');

                }
            }


            return response()->json("success");
        }
        if($obj->type == 'PAYOUT'){
            $webrequest = WebhookRequest::where('transaction_id', $obj->transactionId)
                ->where('gateway_type', 'openpayd-uk')
                ->where('is_pay_in', false)
                ->first();
            if(!$webrequest)
                $webrequest = new WebhookRequest();

            $webrequest->transaction_id = $obj->transactionId;
            $webrequest->status = strtolower($obj->status);
            $webrequest->data = $obj;
            $webrequest->gateway_type = "openpayd-uk";
            $webrequest->is_pay_in = false;

            $webrequest->save();
            return response()->json("success");
        }

        if($obj->type == 'FEE'){
            $webrequest = WebhookRequest::where('transaction_id', $obj->originalTransactionId)
                ->where('gateway_type', 'openpayd-uk')
                ->first();
            if($webrequest) {
                $webrequest->charge = abs($obj->amount->value);
                $webrequest->save();
            }


            return response()->json("success");
        }
        return response()->json("failure");
    }
}
