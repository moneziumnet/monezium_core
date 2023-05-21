<?php

namespace App\Http\Controllers\Deposit;

use Illuminate\Support\Str;

use App\Models\BankGateway;
use App\Models\BankAccount;
use App\Models\Charge;
use App\Models\Currency;
use App\Models\PlanDetail;
use App\Models\DepositBank;
use App\Models\Transaction;
use App\Models\Generalsetting;
use App\Models\Admin;
use App\Models\SubInsBank;
use App\Models\BankPoolAccount;
use App\Models\User;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Validator;
use App\Classes\TribePayment;
use App\Models\WebhookRequest;
use \Spatie\WebhookClient\Models\WebhookCall;

class TribePaymentController extends Controller
{
    public function store(Request $request){
        $gs = Generalsetting::first();
        $client = New Client();
        $user = User::findOrFail($request->user);
        $bankgateway = BankGateway::where('subbank_id', $request->subbank)->first();
        $bankaccount = BankAccount::where('user_id', $request->user)->where('subbank_id', $request->subbank)->where('currency_id', $request->currency)->first();
        if ($bankaccount){
            return redirect()->back()->with(array('warning' => 'This bank account already exists.'));

        }
        $tribepayment = new TribePayment('https://api.wallet.tribepayments.com/api/merchant',$bankgateway->information->API_Key, $bankgateway->information->Secret, false, $bankgateway->information->Des_3_key);
        $response = $tribepayment->getUserKYCStatus('info_monezium_com');
        dd($response);
        $currency = Currency::findOrFail($request->currency);
        $username = explode(' ', $user->name);
        $response = $tribepayment->createAccount('info_monezium_com', $currency->code);
        if($response['status'] == 'error') {
            return redirect()->back()->with(array('warning' => $response['msg']));
        }
        // $response = $tribepayment->createIban($response['account_id'], null, null, null, null, null, null, null, null, null, null, null, null, null, $currency->code, null, null, null, null);
        $response = $tribepayment->createIban($response['account_id'], null, null, null, null, null, null, null, null, null, null, '826', null, null, $currency->code, null, null, null, null);
        if($response['status'] == 'error') {
            return redirect()->back()->with(array('warning' => $response['msg'].' '.$response['description']));
        }
        $response = $tribepayment->getIbanByRequestId($response['iban_request_id']);
        if($response['status'] == 'error') {
            return redirect()->back()->with(array('warning' => $response['msg'].' '.$response['description']));
        }
        if($response['iban_request_status'] == 1 ) {
            $iban = $response['data']['iban'];
        }
        else {
            $iban = '';
        }
        $data = New BankAccount();
        $data->user_id = $request->user;
        $data->subbank_id = $request->subbank;
        $data->iban = $iban;
        $data->swift =  '';
        $data->currency_id = $request->currency;
        $data->save();

        $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
        if(!$chargefee) {
            $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
        }


        $trans = new Transaction();
        $trans->trnx = str_rand();
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id =  defaultCurr();
        $trans->amount      = 0;
        $trans_wallet       = get_wallet($user->id, defaultCurr(), 1);
        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
        $trans->charge      = $chargefee->data->fixed_charge;
        $trans->type        = '-';
        $trans->remark      = 'account-open';
        $trans->details     = trans('Bank Account Create');
        $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
        $trans->save();

        $currency = Currency::findOrFail(defaultCurr());

        mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'type'=>'Bank', 'date_time'=> dateFormat($trans->created_at)], $user);
        send_notification($user->id, 'New Bank Wallet Created for '.($user->company_name ?? $user->name)."\n. Create Pay Fee : ".$trans->charge.$currency->code."\n Transaction ID : ".$trans->trnx, route('admin-user-banks', $user->id));


        user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
        user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);

        return redirect()->back()->with(array('message' => 'Bank Account has been created successfully'));

    }

    public function account_webhook(Request $request) {
        $tribepayment = new WebhookCall();
        $tribepayment->name = 'tribepayment';
        $tribepayment->payload =str2obj($request->getContent());
        $tribepayment->url = route('tribe-account-completed');
        $tribepayment->save();

        $bankaccount = BankAccount::where('iban', $request->iban)->first();
        if($bankaccount && $request->iban_request_status == "5") {
            $bankaccount->swift = $request->bic;
            $bankaccount->save();
        }
    }

    public function pay_out_webhook(Request $request) {
        $tribepayment = new WebhookCall();
        $tribepayment->name = 'tribepayment';
        $tribepayment->payload =str2obj($request->getContent());    
        $tribepayment->url = route('tribe-pay-out-completed');
        $tribepayment->save();

        $obj = json_decode($request->getContent());

        $webrequest = WebhookRequest::where('transaction_id', $obj->transaction_id)
        ->where('gateway_type', 'tribe')
        ->where('is_pay_in', false)
        ->first();
        if(!$webrequest)
            $webrequest = new WebhookRequest();

        $webrequest->transaction_id = $obj->transaction_id;
        $webrequest->status = "completed";
        $webrequest->data = $obj;
        $webrequest->gateway_type = "tribe";
        $webrequest->is_pay_in = false;

        $webrequest->save();
        return response()->json(["status"=>'Ok']);
    }
    
    public function pay_out_reject_webhook(Request $request) {
        $tribepayment = new WebhookCall();
        $tribepayment->name = 'tribepayment';
        $tribepayment->payload =json_decode($request->getContent());    
        $tribepayment->url = route('tribe-pay-out-rejected');
        $tribepayment->save();

        $obj = json_decode($request->getContent());

        $webrequest = WebhookRequest::where('transaction_id', $obj->transaction_id)
        ->where('gateway_type', 'tribe')
        ->where('is_pay_in', false)
        ->first();
        if(!$webrequest)
            $webrequest = new WebhookRequest();

        $webrequest->transaction_id = $obj->transaction_id;
        $webrequest->status = "rejected";
        $webrequest->data = $obj;
        $webrequest->gateway_type = "tribe";
        $webrequest->is_pay_in = false;

        $webrequest->save();
        return response()->json(["status"=>'Ok']);
    }

    public function pay_in_webhook(Request $request) {
        
        $tribepayment = new WebhookCall();
        $tribepayment->name = 'tribepayment';
        $tribepayment->payload =json_decode($request->getContent());    
        $tribepayment->url = route('tribe-pay-in-completed');
        $tribepayment->save();

        $obj = json_decode($request->getContent());


        if(!$obj->reference)
            return response()->json("failure");
        $currency = Currency::where('code', $obj->currency)->first();
        $webrequest = WebhookRequest::where('transaction_id', $obj->transaction_id)
            ->where('gateway_type', 'tribe')
            ->where('is_pay_in', true)
            ->first();
        if(!$webrequest)
            $webrequest = new WebhookRequest();

        $webrequest->transaction_id = $obj->transaction_id;
        $webrequest->sender_name = $obj->sender_account_name;
        $webrequest->sender_address = $obj->sender_address;
        $webrequest->amount = $obj->amount;
        $webrequest->data = $obj;
        $webrequest->currency_id = $currency ? $currency->id : 0;
        $webrequest->status = 'completed';
        $webrequest->reference = $obj->reference;
        $webrequest->gateway_type = "tribe";
        $webrequest->is_pay_in = true;

        $webrequest->save();


        if ($obj->reference == null) {
            $deposit = DepositBank::whereRaw("INSTR('".$obj->transaction_id."', deposit_number) > 0")->first();
            if(!$deposit) {
                $new_deposit = new DepositBank();
                $bankaccount = BankAccount::where('iban', $obj->receiver_iban)->first();
                if(!$bankaccount) {
                    return response()->json(["status"=>'Ok']);
                }

                $new_deposit['deposit_number'] = $obj->transaction_id;
                $new_deposit['user_id'] = $bankaccount->user_id;
                $new_deposit['currency_id'] = $webrequest->currency_id;
                $new_deposit['amount'] = $obj->amount;
                $new_deposit['status'] = "pending";
                $new_deposit['details'] = $obj->reference;
                $new_deposit['sub_bank_id'] = $bankaccount->subbank_id;
                $new_deposit->save();
                $method =  SubInsBank::findOrFail($bankaccount->subbank_id);
                $user = User::findOrFail($bankaccount->user_id);

                send_notification($user->id, 'Bank has been deposited by '.$obj->sender_account_name."\n Amount is ".$currency->symbol.$obj->amount."\n Payment Gateway : Tribe"."\n Transaction ID : ".$obj->transaction_id, route('admin.deposits.bank.index'));
                send_whatsapp($user->id, 'Bank has been deposited by '.$obj->sender_account_name."\n Amount is ".$currency->symbol.$obj->amount."\n Payment Gateway : Openpayd"."\n Transaction ID : ".$obj->transaction_id."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                send_telegram($user->id, 'Bank has been deposited by '.$obj->sender_account_name."\n Amount is ".$currency->symbol.$obj->amount."\n Payment Gateway : Openpayd"."\n Transaction ID : ".$obj->transaction_id."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                send_staff_telegram('Bank has been deposited by '.$obj->sender_account_name."\n Amount is ".$currency->symbol.$obj->amount."\n Payment Gateway : Openpayd"."\n Transaction ID : ".$obj->transaction_id."\nPlease check more details to click this url\n".route('admin.deposits.bank.index'), 'Deposit Bank');

                mailSend('deposit_request',['amount'=>$new_deposit->amount, 'curr' => ($currency ? $currency->code : ' '), 'date_time'=>$new_deposit->created_at ,'type' => 'Bank', 'method'=> $method->name], $user);


            }
        }
        else {
            $deposit = DepositBank::whereRaw("INSTR('".$obj->reference."', deposit_number) > 0")->orWhereRaw("INSTR('".$obj->transaction_id."', deposit_number) > 0")->first();
            if(!$deposit) {
                $new_deposit = new DepositBank();
                $bankaccount = BankAccount::where('iban', $obj->receiver_iban)->first();

                $new_deposit['deposit_number'] = $obj->transaction_id;
                $new_deposit['user_id'] = $bankaccount->user_id;
                $new_deposit['currency_id'] = $webrequest->currency_id;
                $new_deposit['amount'] = $obj->amount;
                $new_deposit['status'] = "pending";
                $new_deposit['details'] = $obj->reference;
                $new_deposit['sub_bank_id'] = $bankaccount->subbank_id;;
                $new_deposit->save();
                $method =  SubInsBank::findOrFail($bankaccount->subbank_id);
                $user = User::findOrFail($bankaccount->user_id);

                mailSend('deposit_request',['amount'=>$new_deposit->amount, 'curr' => ($currency ? $currency->code : ' '), 'date_time'=>$new_deposit->created_at ,'type' => 'Bank', 'method'=> $method->name ], $user);
                send_notification($user->id, 'Bank has been deposited by '.$obj->sender_account_name."\n Amount is ".$currency->symbol.$obj->amount->value."\n Payment Gateway : Openpayd"."\n Transaction ID : ".$obj->transaction_id, route('admin.deposits.bank.index'));
                send_whatsapp($user->id, 'Bank has been deposited by '.$obj->sender_account_name."\n Amount is ".$currency->symbol.$obj->amount->value."\n Payment Gateway : Openpayd"."\n Transaction ID : ".$obj->transaction_id."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                send_telegram($user->id, 'Bank has been deposited by '.$obj->sender_account_name."\n Amount is ".$currency->symbol.$obj->amount->value."\n Payment Gateway : Openpayd"."\n Transaction ID : ".$obj->transaction_id."\nPlease check more details to click this url\n".route('user.depositbank.index'));
                send_staff_telegram('Bank has been deposited by '.$obj->sender_account_name."\n Amount is ".$currency->symbol.$obj->amount->value."\n Payment Gateway : Openpayd"."\n Transaction ID : ".$obj->transaction_id."\nPlease check more details to click this url\n".route('admin.deposits.bank.index'), 'Deposit Bank');

            }
        }
        return response()->json(["status"=>'Ok']);

    }

    public function pay_in_reject_webhook(Request $request) {
        
    }
}
