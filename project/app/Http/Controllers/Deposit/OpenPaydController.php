<?php

namespace App\Http\Controllers\Deposit;

use Illuminate\Support\Str;
use App\Classes\GeniusMailer;
use App\Models\BankGateway;
use App\Models\BankAccount;
use App\Models\Currency;
use App\Models\PlanDetail;
use App\Models\DepositBank;
use App\Models\Generalsetting;
use App\Models\Admin;
use App\Models\SubInsBank;
use App\Models\BankPoolAccount;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Validator;

class OpenPaydController extends Controller
{
    public function store(Request $request){
        $client = New Client();
        $user = User::findOrFail($request->user);
        $bankgateway = BankGateway::where('subbank_id', $request->subbank)->first();
        $bankaccount = BankAccount::where('user_id', $request->user)->where('subbank_id', $request->subbank)->where('currency_id', $request->currency)->first();
        if ($bankaccount){
            return redirect()->back()->with(array('warning' => 'This bank account already exists.'));

        }
        try {
            $response = $client->request('POST', 'https://sandbox.openpayd.com/api/oauth/token?grant_type=client_credentials', [
                'headers' => [
                   'Accept'=> 'application/json',
                  'Authorization' => 'Basic '.$bankgateway->information->Auth,
                  'Content-Type' => 'application/x-www-form-urlencoded',
                ],
              ]);
            $res_body = json_decode($response->getBody());
            $auth_token = $res_body->access_token;
            $accounter_id = $res_body->accountHolderId;
        } catch (\Throwable $th) {
             return redirect()->back()->with(array('warning' => 'Some Value is incorrect'));
        }
        try {
            $currency = Currency::whereId($request->currency)->first();
            $response = $client->request('POST', 'https://sandbox.openpayd.com/api/accounts', [
                'body' => '{"currency":"'.$currency->code.'","friendlyName":"Billing Account('.$currency->code.')"}',
                'headers' => [
                  'Accept' => 'application/json',
                  'Authorization' => 'Bearer '.$auth_token,
                  'Content-Type' => 'application/json',
                  'x-account-holder-id' => $accounter_id,
                ],
            ]);
            $res_body = json_decode($response->getBody());
            $internal_id = $res_body->internalAccountId;
        } catch (\Throwable $th) {
             return redirect()->back()->with(array('warning' => 'Some Value is incorrect'));
        }

        try {
            $response = $client->request('GET', 'https://sandbox.openpayd.com/api/bank-accounts?internalAccountId='.$internal_id, [
                'headers' => [
                  'Accept' => 'application/json',
                  'Authorization' => 'Bearer '.$auth_token,
                  'Content-Type' => 'application/json',
                  'x-account-holder-id' => $accounter_id,
                ],
            ]);
            $res_body = json_decode($response->getBody())[0];

            $bic_swift = $res_body->bic;
            $iban = $res_body->iban;
        } catch (\Throwable $th) {
             return redirect()->back()->with(array('warning' => 'Some Value is incorrect'));
        }



        $data = New BankAccount();
        $data->user_id = $request->user;
        $data->subbank_id = $request->subbank;
        $data->iban = $iban;
        $data->swift = $bic_swift;
        $data->currency_id = $request->currency;
        $data->save();
        return redirect()->back()->with(array('message' => 'Bank Account has been created successfully'));

    }

    public function transfer(Request $request) {
        $user = auth()->user();
        if($user->payment_fa_yn == 'Y') {
            if ($user->two_fa_code != $request->otp_code) {
                return redirect()->back()->with('unsuccess','Verification code is not matched.');
            }
        }
        $other_bank_limit =Generalsetting::first()->other_bank_limit;
        if ($request->amount >= $other_bank_limit) {
            $rules = [
                'document' => 'required|mimes:xls,xlsx,pdf,jpg,png'
            ];
        }
        else {
            $rules = [
                'document' => 'mimes:xls,xlsx,pdf,jpg,png'
            ];
        }


        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with('unsuccess',$validator->getMessageBag()->toArray()['document'][0]);
        }

        $currency = Currency::where('id',$request->currency_id)->first();
        $amountToAdd = $request->amount/$currency->rate;
        $user = auth()->user();
        $subbank = SubInsBank::where('id', $request->bank)->first();
        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'deposit')->first();
        $dailydeposit = DepositBank::where('user_id', $user->id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus('complete')->sum('amount');
        $monthlydeposit = DepositBank::where('user_id', $user->id)->whereMonth('created_at', '=', date('m'))->whereStatus('complete')->sum('amount');

        if ( $request->amount < $global_range->min ||  $request->amount > $global_range->max) {
           return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min );

        }
        if ($subbank->max_limit == 0) {
            if ( $request->amount < $subbank->min_limit ) {
                return redirect()->back()->with('unsuccess','Your amount is not in defined bank limit range.  Min value is '.$subbank->min_limit );

             }
        }
        else {

            if ( $request->amount < $subbank->min_limit ||  $request->amount > $subbank->max_limit) {
                return redirect()->back()->with('unsuccess','Your amount is not in defined bank limit range. Max value is '.$subbank->max_limit.' and Min value is '.$subbank->min_limit );

             }
        }

        if($dailydeposit > $global_range->daily_limit){
            return redirect()->back()->with('unsuccess','Daily deposit limit over.');
        }

        if($monthlydeposit > $global_range->monthly_limit){
            return redirect()->back()->with('unsuccess','Monthly deposit limit over.');
        }


        $customer_bank = BankAccount::whereUserId($user->id)->where('subbank_id',$request->bank)->where('currency_id', $request->currency_id)->first();
        $bankgateway = BankGateway::where('subbank_id', $request->bank)->first();
        $subbank_account = BankPoolAccount::where('bank_id', $request->bank)->where('currency_id', $request->currency_id)->first();
        if (!$subbank_account) {
            return redirect()->back()->with('unsuccess','Bank account for this currency does not exist');
        }
        $subbank = SubInsBank::where('id', $request->bank)->first();
        $subuser = Admin::where('id', $subbank->ins_id)->first();
        $client = New Client();

        try {
            $response = $client->request('POST', 'https://sandbox.openpayd.com/api/oauth/token?grant_type=client_credentials', [
                'headers' => [
                   'Accept'=> 'application/json',
                  'Authorization' => 'Basic '.$bankgateway->information->Auth,
                  'Content-Type' => 'application/x-www-form-urlencoded',
                ],
              ]);
            $res_body = json_decode($response->getBody());
            $auth_token = $res_body->access_token;
            $accounter_id = $res_body->accountHolderId;
        } catch (\Throwable $th) {
             return redirect()->back()->with(array('warning' => 'Some Valuen is incorrect '));
        }


        try {
            $response = $client->request('GET', 'https://sandbox.openpayd.com/api/accounts?iban='.$customer_bank->iban, [
                'headers' => [
                  'Accept' => 'application/json',
                  'Authorization' => 'Bearer '.$auth_token,
                  'Content-Type' => 'application/json',
                  'x-account-holder-id' => $accounter_id,
                ],
              ]);
              $res_body = json_decode($response->getBody())->content[0];

            $account_id = $res_body->id;
            $amount = $res_body->availableBalance->value;
            if ($amount < $request->amount) {
                return redirect()->back()->with(array('warning' => 'Insufficient Balance.'));
            }
        } catch (\Throwable $th) {
             return redirect()->back()->with(array('warning' => 'Some Value is incorrect'));
        }

        try {
            $response = $client->request('POST', 'https://sandbox.openpayd.com/api/beneficiaries', [
                'body' => '{
                    "beneficiaryType":"CORPORATE",
                    "friendlyName":"'.$subbank->name.'",
                    "companyName":"'.$subbank->name.'"
                }',
                'headers' => [
                  'Accept' => 'application/json',
                  'Authorization' => 'Bearer '.$auth_token,
                  'Content-Type' => 'application/json',
                  'x-account-holder-id' => $accounter_id,
                ],
            ]);
            $res_body = json_decode($response->getBody());
            $beneficiary_id = $res_body->id;
        } catch (\Throwable $th) {
             return redirect()->back()->with(array('warning' => 'Some Value is incorrect'));
        }

        try {
            $response = $client->request('POST', 'https://sandbox.openpayd.com/api/beneficiaries/'.$beneficiary_id.'/bank-beneficiaries', [
                'body' => '{
                    "paymentTypes":["SWIFT"],
                    "bankAccountCurrency":"'.$currency->code.'",
                    "beneficiaryType":"CORPORATE",
                    "beneficiaryCountry":"'.substr($subbank_account->iban, 0,2).'",
                    "bankAccountCountry":"'.substr($subbank_account->iban, 0,2).'",
                    "iban":"'.$subbank_account->iban.'",
                    "bic":"'.$subbank_account->swift.'",
                    "bankAccountHolderName":"'.$subbank->name.'",
                    "companyName":"'.$subbank->name.'"
                }',
                'headers' => [
                  'Accept' => 'application/json',
                  'Authorization' => 'Bearer '.$auth_token,
                  'Content-Type' => 'application/json',
                  'x-account-holder-id' => $accounter_id,
                ],
            ]);
            $res_body = json_decode($response->getBody());
            $beneficiary_id = $res_body->id;
        } catch (\Throwable $th) {
             return redirect()->back()->with(array('warning' => 'Some Value is incorrect'));
        }

        try {
            $response = $client->request('POST', 'https://sandbox.openpayd.com/api/transactions/bank-payouts', [
                'body' => '{
                    "accountId":"'.$account_id.'",
                    "beneficiaryId":"'.$beneficiary_id.'",
                    "paymentType":"SWIFT",
                    "amount":
                    {"currency":"'.$currency->code.'","value":"'.$request->amount.'"},
                    "reference":"'. $request->details.'"
                }',
                'headers' => [
                  'Accept' => 'application/json',
                  'Authorization' => 'Bearer '.$auth_token,
                  'Content-Type' => 'application/json',
                  'x-account-holder-id' => $accounter_id,
                ],
            ]);
            $res_body = json_decode($response->getBody());
            $transaction_id = $res_body->id;
        } catch (\Throwable $th) {
             return redirect()->back()->with(array('warning' => 'Some Value is incorrect'));
        }


        $txnid = Str::random(4).time();
        $deposit = new DepositBank();

        if ($file = $request->file('document'))
        {
            $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/doc',$name);
            $deposit['document'] = $name;
        }

        $deposit['deposit_number'] = Str::random(12);
        $deposit['user_id'] = auth()->id();
        $deposit['currency_id'] = $request->currency_id;
        $deposit['amount'] = $amountToAdd;
        $deposit['method'] = $request->method;
        $deposit['sub_bank_id'] = $request->bank;
        $deposit['txnid'] = $transaction_id;
        $deposit['details'] = $request->details;
        $deposit['status'] = "pending";
        $deposit->save();

        $gs =  Generalsetting::findOrFail(1);
        $user = auth()->user();
        if($gs->is_smtp == 1)
        {
            $data = [
                'to' => $user->email,
                'type' => "Deposit",
                'cname' => $user->name,
                'oamount' => $amountToAdd,
                'aname' => "",
                'aemail' => "",
                'wtitle' => "",
            ];

            $mailer = new GeniusMailer();
            $mailer->sendAutoMail($data);
        }
        else
        {
           $to = $user->email;
           $subject = " You have deposited successfully.";
           $msg = "Hello ".$user->name."!\nYou have invested successfully.\nThank you.";
           $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
           mail($to,$subject,$msg,$headers);
        }

        return redirect()->route('user.depositbank.create')->with('success','Deposit amount '.$request->amount.' ('.$currency->code.') successfully!');
    }
}
