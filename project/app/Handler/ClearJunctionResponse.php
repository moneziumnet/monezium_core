<?php

namespace App\Handler;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BankGateway;
use App\Models\BankAccount;

class ClearJunctionResponse extends Controller
{
    public function index(Request $request)
    {
        $obj = json_decode($request->getContent());
        
        $currency = Currency::where('code', $obj->currency)->first();
        $webrequest = WebhookRequest::where('reference', $obj->orderReference)
            ->where('gateway_type', 'clearjunction')
            ->first();
        if(!$webrequest)
            $webrequest = new WebhookRequest();
        
        $webrequest->transaction_id = $obj->clientOrder;
        $webrequest->sender_name = "";
        $webrequest->sender_address = $obj->payer->address->addressOneString;
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
        $webrequest->reference = $obj->orderReference;
        $webrequest->gateway_type = "clearjunction";
        $webrequest->save();

        $deposit = DepositBank::whereRaw("INSTR('".$obj->orderReference."', deposit_number) > 0")->first();
        if(!$deposit) {
            $new_deposit = new DepositBank();
            $user = $this->getUser($obj->clientOrder);

            if(!$user)
                return response()->json("failure");

            $new_deposit['deposit_number'] = $obj->orderReference;
            $new_deposit['user_id'] = $user->id;
            $new_deposit['currency_id'] = $webrequest->currency_id;
            $new_deposit['amount'] = $obj->amount->value;
            $new_deposit['status'] = "pending";
            $new_deposit['sub_bank_id'] = null;
            $new_deposit->save();
        }

        return response()->json("success");
    }

    public function getToken($request, $bankgateway) {
        $secret = hash('sha512', $bankgateway->information->api_password);
        $datetime = new DateTime();
        $now = $datetime->format(DateTime::ATOM);
        $signature = hash('sha512', mb_strtoupper($bankgateway->information->API_Key).$now.mb_strtoupper($secret).mb_strtoupper($request));
        return array($signature, $now);
    }
    
    public function getUser($orderid) {
        $gateway_list = BankGateway::where('keyword', 'railsbank')->get();
        foreach($gateway_list as $gateway_item) {
            try {
                $param = $this->getToken('{}', $gateway_item);
                $response = $client->request('GET',  $this->url.'gate/allocate/v2/status/iban/clientOrder/'.$orderid, [
                    'body' => '{}',
                    'headers' => [
                        'Accept'=> '*/*',
                        'X-API-KEY' => $gateway_item->information->API_Key,
                        'Authorization' => 'Bearer '.$param[0],
                        'Date' => $param[1],
                        'Content-Type' => 'application/json',
                    ],
                ]);
                $res_body = json_decode($response->getBody());
                $iban = $res_body->iban;
                
                $bankaccount = BankAccount::where('iban', $iban)->first();
                return $bankaccount->user_id;
            } catch (\Exception $e) {
                continue;
            }
        }
        return null;
    }
}