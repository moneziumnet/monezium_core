<?php
namespace App\Handler;

use App\Models\User;
use GuzzleHttp\Client;
use App\Models\Currency;
use App\Models\BankAccount;
use App\Models\BankGateway;
use App\Models\BalanceTransfer;
use App\Models\DepositBank;
use Illuminate\Http\Request;
use App\Models\WebhookRequest;
use Spatie\WebhookClient\WebhookConfig;
use Symfony\Component\HttpFoundation\Response;
use Spatie\WebhookClient\WebhookResponse\RespondsToWebhook;

class SwanResponse implements RespondsToWebhook
{
    public function respondToValidWebhook(Request $request, WebhookConfig $config): Response
    {
        $obj = json_decode($request->getContent());

        if(!isset($obj->resourceId)) {
            return response()->json('error');
        }
        $webrequest = WebhookRequest::where('transaction_id', $obj->resourceId)
            ->where('gateway_type', 'swan')
            ->where('is_pay_in', true)
            ->first();

        if(!$webrequest)
            $webrequest = new WebhookRequest();

        $webrequest->transaction_id = $obj->resourceId;
        switch($obj->eventType) {
            case "transaction-pending":
                $webrequest->status = "processing";
                break;
            case "Transaction.Booked":
                $webrequest->status = "completed";
                break;
            case "Transaction.Canceled":
            case "Transaction.Deleted":
            case "Transaction.Rejected":
                $webrequest->status = "failed";
                break;
            default:
                return response()->json('error');
        }

        $webrequest->gateway_type = "swan";
        $balance_transfer = BalanceTransfer::where('transaction_no', $obj->resourceId)->first();
        if($balance_transfer) {
            $webrequest->is_pay_in = false;
            $webrequest->save();
            return response()->json("success");
        } else {
            $webrequest->is_pay_in = true;

            $gateway_list = BankGateway::where('keyword', 'swan')->get();

            foreach($gateway_list as $gateway_item) {
                $client = New Client();
                try {
                    $options = [
                        'multipart' => [
                        [
                            'name' => 'client_id',
                            'contents' => $gateway_item->information->client_id
                        ],
                        [
                            'name' => 'client_secret',
                            'contents' => $gateway_item->information->client_secret
                        ],
                        [
                            'name' => 'grant_type',
                            'contents' => 'client_credentials'
                        ]
                    ]];
                    $response = $client->request('POST', 'https://oauth.swan.io/oauth2/token', $options);
                    $res_body = json_decode($response->getBody());
                    $access_token = $res_body->access_token;

                    $body = '{"query":"query Transaction($id: ID!) {\\n  transaction(id: $id) {\\n    id\\n    reference\\n    counterparty\\n    amount {\\n      currency\\n      value\\n    }\\n    account {\\n      IBAN\\n    }\\n  }\\n}","variables":{"id": "'.$obj->resourceId.'"}}';
                    $headers = [
                        'Authorization' => 'Bearer '.$access_token,
                        'Content-Type' => 'application/json'
                        ];
                    $response = $client->request('POST', 'https://api.swan.io/sandbox-partner/graphql', [
                        'body' => $body,
                        'headers' => $headers
                    ]);
                    $details = json_decode($response->getBody())->data->transaction;

                    $webrequest->sender_name = $details->counterparty;
                    $webrequest->sender_address = " ";
                    $webrequest->reference = $details->reference;
                    $webrequest->amount = $details->amount->value;
                    $currency = Currency::where('code', $details->amount->currency)->first();
                    $webrequest->currency_id = $currency ? $currency->id : 0;

                    $webrequest->save();

                    $deposit = DepositBank::whereRaw("INSTR('".$details->reference."', deposit_number) > 0")->orWhereRaw("INSTR('".$obj->resourceId."', deposit_number) > 0")->first();
                    if(!$deposit) {
                        $new_deposit = new DepositBank();
                        $iban = BankAccount::where('iban', $details->account->IBAN)->first();
                        $user = User::findOrFail($iban->user_id);

                        if(!$iban)
                            return response()->json("failure");

                        $new_deposit['deposit_number'] = $obj->resourceId;
                        $new_deposit['user_id'] = $iban->user_id;
                        $new_deposit['currency_id'] = $webrequest->currency_id;
                        $new_deposit['amount'] = $details->amount->value;
                        $new_deposit['status'] = "pending";
                        $new_deposit['sub_bank_id'] = null;
                        $new_deposit->save();
                        $subbank = SubInsBank::findOrFail($iban->subbank_id);

                        mailSend('deposit_request',['amount'=>$new_deposit->amount, 'curr' => ($currency ? $currency->code : ' '), 'date_time'=>$new_deposit->created_at ,'type' => 'Bank', 'method'=> $subbank->name ], $user);
                        send_notification($iban->user_id, 'Bank has been deposited by '.$details->counterparty.'. Please check.', route('admin.deposits.bank.index'));
                        send_whatsapp($iban->user_id, 'Bank has been deposited by '.$details->counterparty."\n Amount is ".$currency->symbol.$details->amount->value."\n Payment Gateway : Swan"."\n Transaction ID : ".$obj->resourceId."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                        send_telegram($iban->user_id, 'Bank has been deposited by '.$details->counterparty."\n Amount is ".$currency->symbol.$details->amount->value."\n Payment Gateway : Swan"."\n Transaction ID : ".$obj->resourceId."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                        send_staff_telegram('Bank has been deposited by '.$details->counterparty."\n Amount is ".$currency->symbol.$details->amount->value."\n Payment Gateway : Swan"."\n Transaction ID : ".$obj->resourceId."\nPlease check more details to click this url\n".route('admin.deposits.bank.index'), 'Deposit Bank');
                    }

                    return response()->json("success");
                } catch (\Exception $e) {
                    continue;
                }
            }
            return response()->json("error");
        }
    }
}
