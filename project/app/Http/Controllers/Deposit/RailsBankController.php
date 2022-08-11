<?php

namespace App\Http\Controllers\Deposit;

use Illuminate\Support\Str;
use App\Classes\GeniusMailer;
use App\Models\BankGateway;
use App\Models\BankAccount;
use App\Models\Currency;
use App\Models\PlanDetail;
use App\Models\DepositBank;
use App\Models\Admin;
use App\Models\SubInsBank;
use App\Models\BankPoolAccount;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Auth;

class RailsBankController extends Controller
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
            $response = $client->request('POST', 'https://play.railsbank.com/v1/customer/endusers', [
                'body' => '{
                    "person": {
                      "name": "'.$user->name.'",
                      "email": "'.$user->email.'",
                      "address": { "address_iso_country": "US" }
                    }
                }',
                'headers' => [
                    'Accept'=> 'application/json',
                    'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
                    'Content-Type' => 'application/json',
                ],
            ]);
            $enduser = json_decode($response->getBody())->enduser_id;
        } catch (\Throwable $ex) {
            //throw $th;
            return redirect()->back()->with(array('warning' => 'Some Value is incorrect'));
        }

        try {
            $response = $client->request('POST', 'https://play.railsbank.com/v1/customer/ledgers', [
                'body' => '{
                    "holder_id": "'.$enduser.'",
                    "partner_product": "ExampleBank-EUR-1",
                    "asset_class": "currency",
                    "asset_type": "eur",
                    "ledger-type": "ledger-type-single-user",
                    "ledger-who-owns-assets": "ledger-assets-owned-by-me",
                    "ledger-primary-use-types": ["ledger-primary-use-types-payments"],
                    "ledger-t-and-cs-country-of-jurisdiction": "US"
                  }',
                'headers' => [
                    'Accept'=> 'application/json',
                    'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
                    'Content-Type' => 'application/json',
                ],
            ]);
            $ledger = json_decode($response->getBody())->ledger_id;
        } catch (\Throwable $ex) {
            return redirect()->back()->with(array('warning' => 'Some Value is incorrect'));
        }

        try {
            $response = $client->request('GET', 'https://play.railsbank.com/v1/customer/ledgers/'.$ledger.'/wait', [
                'headers' => [
                    'Accept'=> 'application/json',
                    'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
                    'Content-Type' => 'application/json',
                ],
            ]);
            $bic_swift = json_decode($response->getBody())->bic_swift;
            $iban = json_decode($response->getBody())->iban;
        } catch (\Throwable $ex) {
            //throw $th;
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

        // return response()->json('Bank Account has been created successfully');
        // $response = $client->request('POST', 'https://play.railsbank.com/dev/customer/transactions/receive', [
        //     'body' => '{
        //         "iban": "'.$iban.'",
        //         "bic-swift": "'.$bic_swift.'",
        //         "amount": 10
        //       }'
        //     ,
        //     'headers' => [
        //         'Accept'=> 'application/json',
        //         'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
        //         'Content-Type' => 'application/json',
        //         ],
        // ]);
////////////////////////////// test mode end /////////////////////////////////////////////////////
        // $response = $client->request('POST', 'https://play.railsbank.com/v1/customer/ledgers/'.$ledger.'/external-accounts', [
        //     'body' => '{
        //         "number": "'.$request->banknumber.'",
        //         "bank_code": "'.$request->bankcode.'",
        //         "name": "'.$user->name.'",
        //         "account_type": "routing-number",
        //         "bank_name": "'.$request->bankname.'",
        //         "type": "",
        //         "legal_type": "person",
        //         "external_account_meta": {}
        //       }'
        //     ,
        //     'headers' => [
        //         'Accept'=> 'application/json',
        //         'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
        //         'Content-Type' => 'application/json',
        //         ],
        // ]);
        // $external_account = json_decode($response->getBody())->external_account_id;

        // $response = $client->request('POST','https://play.railsbank.com/v1/customer/beneficiaries', [
        //     'body' => '{
        //         "holder_id": "'.$enduser.'",
        //         "asset_class": "currency",
        //         "asset_type": "eur",
        //         "iban": "'.$subbank->iban.'",
        //         "bic_swift": "'.$subbank->swift.'",
        //         "person": {
        //           "name": "'.$subuser->name.'",
        //           "email": "'.$subuser->email.'",
        //           "address": { "address_iso_country": "US" }
        //         }
        //       }',
        //     'headers' => [
        //         'Accept'=> 'application/json',
        //         'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
        //         'Content-Type' => 'application/json',
        //     ],
        // ]);
        // $beneficiary = json_decode($response->getBody())->beneficiary_id;

        // $response = $client->request('POST', 'https://play.railsbank.com/v1/customer/transactions', [
        //     'body' => '{
        //         "ledger_from_id": "'.$ledger.'",
        //         "beneficiary_id": "'.$beneficiary.'",
        //         "payment_type": "payment-type-EU-SEPA-Step2",
        //         "amount": "'.$request->amount.'"
        //       }',
        //     'headers' => [
        //        'Accept'=> 'application/json',
        //       'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
        //       'Content-Type' => 'application/json',
        //     ],
        //   ]);
        // $transaction = json_decode($response->getBody())->transaction_id;

        // return redirect()->route('user.depositbank.create')->with('success','Deposit amount '.$request->amount.' ('.$currency->code.') successfully!');
    }
    public function transfer(Request $request) {
        $currency = Currency::where('id',$request->currency_id)->first();
        $amountToAdd = $request->amount/$currency->rate;
        $user = auth()->user();
        $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'deposit')->first();
        $dailydeposit = DepositBank::where('user_id', $user->id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus('complete')->sum('amount');
        $monthlydeposit = DepositBank::where('user_id', $user->id)->whereMonth('created_at', '=', date('m'))->whereStatus('complete')->sum('amount');

        if ( $request->amount < $global_range->min ||  $request->amount > $global_range->max) {
           return redirect()->back()->with('unsuccess','Your amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min );

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
            $response = $client->request('GET','https://play.railsbank.com/v1/customer/ledgers?account_number='.$customer_bank->iban, [
                'headers' => [
                    'Accept'=> 'application/json',
                    'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
                    'Content-Type' => 'application/json',
                ],
            ]);
            $enduser = json_decode($response->getBody())[0]->holder_id;
            $amount = json_decode($response->getBody())[0]->amount;
            $ledger = json_decode($response->getBody())[0]->ledger_id;
            if ($amount < $request->amount) {
                return redirect()->back()->with(array('warning' => 'Insufficient Balance.'));
            }
        } catch (\Throwable $th) {
            // return $th->getMessage();
            return redirect()->back()->with(array('warning' => $th->getMessage()));
        }


        try {

            $response = $client->request('POST','https://play.railsbank.com/v1/customer/beneficiaries', [
                'body' => '{
                    "holder_id": "'.$enduser.'",
                    "asset_class": "currency",
                    "asset_type": "eur",
                    "iban": "'.$subbank_account->iban.'",
                    "bic_swift": "'.$subbank_account->swift.'",
                    "person": {
                    "name": "'.$subuser->name.'",
                    "email": "'.$subuser->email.'",
                    "address": { "address_iso_country": "US" }
                    }
                }',
                'headers' => [
                    'Accept'=> 'application/json',
                    'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
                    'Content-Type' => 'application/json',
                ],
            ]);

            $beneficiary = json_decode($response->getBody())->beneficiary_id;
        } catch (\Throwable $th) {
                    // return $th->getMessage();
            return redirect()->back()->with(array('warning' => '1:'.$th->getMessage()));
        }
        try {
            $response = $client->request('POST', 'https://play.railsbank.com/v1/customer/transactions', [
                'body' => '{
                    "ledger_from_id": "'.$ledger.'",
                    "beneficiary_id": "'.$beneficiary.'",
                    "payment_type": "payment-type-EU-SEPA-Step2",
                    "amount": "'.$request->amount.'"
                  }',
                'headers' => [
                   'Accept'=> 'application/json',
                  'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
                  'Content-Type' => 'application/json',
                ],
              ]);
            $transaction = json_decode($response->getBody())->transaction_id;
        } catch (\Throwable $th) {
            return redirect()->back()->with(array('warning' => $th->getMessage()));
        }

        return $transaction;


        $txnid = Str::random(4).time();
        $deposit = new DepositBank();
        $deposit['deposit_number'] = Str::random(12);
        $deposit['user_id'] = auth()->id();
        $deposit['currency_id'] = $request->currency_id;
        $deposit['amount'] = $amountToAdd;
        $deposit['method'] = $request->method;
        $deposit['sub_bank_id'] = $request->bank;
        $deposit['txnid'] = $request->txnid;
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
