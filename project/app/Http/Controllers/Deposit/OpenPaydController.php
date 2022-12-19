<?php

namespace App\Http\Controllers\Deposit;

use Illuminate\Support\Str;
use App\Classes\GeniusMailer;
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
            $response = $client->request('POST', 'https://secure-mt.openpayd.com/api/oauth/token?grant_type=client_credentials', [
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
            return redirect()->back()->with(array('warning' => json_encode($th->getMessage())));
        }
        $country = Country::findOrFail($user->country);

        if ($auth_token == null || $accounter_id == null) {
            return redirect()->back()->with(array('warning' => 'Sorry, You can\'t create New Bank Account succesfully because openpayd\'s accounter_id or token is not generated by OPENPAY API. Please create again.'));
        }

        if (!$user->holder_id){
        try {
            $currency = Currency::whereId($request->currency)->first();

            if(!isset($user->company_name)) {
                $body = '{
                    "individual": {
                         "address": {
                              "addressLine1": "'.$user->address.'",
                              "city": "'.$user->city.'",
                              "postCode": "'.$user->zip.'",
                              "country": "'.$country->iso2.'"
                         },
                         "firstName": "'.explode(" ",$user->name)[0].'",
                         "lastName": "'.explode(" ",$user->name)[1].'",
                         "dateOfBirth": "'.$user->dob.'",
                         "email": "'.$user->email.'"
                    },
                    "clientType": "INDIVIDUAL",
                    "friendlyName": "'.$user->name.'"
               }';
            }
            else {
                $company_country = Country::findOrFail($user->company_country);

               $body = '{
                "company": {
                     "registeredAddress": {
                          "addressLine1": "'.$user->company_address.'",
                          "city": "'.$user->company_city.'",
                          "postCode": "'.$user->company_zipcode.'",
                          "country": "'.$company_country->iso2.'"
                     },
                     "tradingAddress": {
                          "addressLine1": "'.$user->company_address.'",
                          "city": "'.$user->company_city.'",
                          "postCode": "'.$user->company_zipcode.'",
                          "country": "'.$company_country->iso2.'"
                     },
                     "companyName": "'.$user->company_name.'",
                     "companyType": "'.$user->company_type.'",
                     "registrationNumber": "'.$user->company_reg_no.'",
                     "contactName": "'.$user->name.'",
                     "email": "'.$user->email.'",
                     "phone": "'.$user->phone.'"
                },
                "clientType": "BUSINESS",
                "friendlyName": "'.$user->company_name.'"
           }';
            }
            // dd($body);
            $response = $client->request('POST', 'https://secure-mt.openpayd.com/api/linkedClient', [
                'body' => $body,
                'headers' => [
                  'Accept' => 'application/json',
                  'Authorization' => 'Bearer '.$auth_token,
                  'Content-Type' => 'application/json',
                  'x-account-holder-id' => $accounter_id,
                ],
            ]);
            $res_body = json_decode($response->getBody());
            $linked_accountid = $res_body->accountHolderId;
        } catch (\Throwable $th) {
            return redirect()->back()->with(array('warning' => json_encode($th->getMessage())));
        }
        $user->holder_id = $linked_accountid;
        $user->update();
    }
    else {
        $linked_accountid = $user->holder_id;
    }

    if ($linked_accountid == null) {
        return redirect()->back()->with(array('warning' => 'Sorry, You can\'t create New Bank Account succesfully because openpayd\'s linked_accountid is not generated by OPENPAY API. Please try again.'));
    }
        try {
            $currency = Currency::whereId($request->currency)->first();
            $response = $client->request('POST', 'https://secure-mt.openpayd.com/api/accounts', [
                'body' => '{"currency":"'.$currency->code.'","friendlyName":"Billing Account('.$currency->code.')"}',
                'headers' => [
                  'Accept' => 'application/json',
                  'Authorization' => 'Bearer '.$auth_token,
                  'Content-Type' => 'application/json',
                  'x-account-holder-id' => $linked_accountid,
                ],
            ]);
            $res_body = json_decode($response->getBody());
            $internal_id = $res_body->internalAccountId;
        } catch (\Throwable $th) {
            return redirect()->back()->with(array('warning' => json_encode($th->getMessage())));
        }
        if ($internal_id == null) {
            return redirect()->back()->with(array('warning' => 'Sorry, You can\'t create New Bank Account succesfully because openpayd\'s internal_id is not generated by OPENPAY API. Please try again.'));
        }

        try {
            $response = $client->request('GET', 'https://secure-mt.openpayd.com/api/bank-accounts?internalAccountId='.$internal_id, [
                'headers' => [
                  'Accept' => 'application/json',
                  'Authorization' => 'Bearer '.$auth_token,
                  'Content-Type' => 'application/json',
                  'x-account-holder-id' => $linked_accountid,
                ],
            ]);
            $res_body = json_decode($response->getBody())[0];

            $bic_swift = $res_body->bic;
            $iban = $res_body->iban;
        } catch (\Throwable $th) {
            return redirect()->back()->with(array('warning' => json_encode($th->getMessage())));
        }


        if ($iban == null || $bic_swift == null || $iban == '' || $bic_swift == '' ) {
            return redirect()->back()->with(array('warning' => 'Sorry, You can not create New Bank Account succesfully because openpayd\'s internal_id is not generated by OPENPAY API. Please try again.'));
        }
        $data = New BankAccount();
        $data->user_id = $request->user;
        $data->subbank_id = $request->subbank;
        $data->iban = $iban;
        $data->swift = $bic_swift;
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
        $trans->amount      = $chargefee->data->fixed_charge;
        $trans_wallet       = get_wallet($user->id, defaultCurr(), 1);
        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
        $trans->charge      = 0;
        $trans->type        = '-';
        $trans->remark      = 'bank_account_create';
        $trans->details     = trans('Bank Account Create');
        $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"System Account"}';
        $trans->save();

        user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
        user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);

        return redirect()->back()->with(array('message' => 'Bank Account has been created successfully'));

    }

    public function master_store(Request $request){
        $rules = [
            'currency' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with(array('errors' => $validator->getMessageBag()->toArray()));
        }
        $client = New Client();
        $bankgateway = BankGateway::where('subbank_id', $request->subbank)->first();
        $bankaccount = BankPoolAccount::where('bank_id', $request->subbank)->where('currency_id', $request->currency)->first();
        if ($bankaccount){
            return redirect()->back()->with(array('warning' => 'This bank account already exists.'));

        }
        try {
            $response = $client->request('POST', 'https://secure-mt.openpayd.com/api/oauth/token?grant_type=client_credentials', [
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
             return redirect()->back()->with(array('warning' => json_encode($th->getMessage())));
        }


        if ($auth_token == null || $accounter_id == null) {
            return redirect()->back()->with(array('warning' => 'Sorry, You can\'t create New Bank Account succesfully because openpayd\'s accounter_id or token is not generated by OPENPAY API. Please create again.'));
        }

        try {
            $currency = Currency::whereId($request->currency)->first();
            $response = $client->request('POST', 'https://secure-mt.openpayd.com/api/accounts', [
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
             return redirect()->back()->with(array('warning' => json_encode($th->getMessage())));
        }

        if ($internal_id == null) {
            return redirect()->back()->with(array('warning' => 'Sorry, You can\'t create New Bank Account succesfully because openpayd\'s internal_id is not generated by OPENPAY API. Please try again.'));
        }

        try {
            $response = $client->request('GET', 'https://secure-mt.openpayd.com/api/bank-accounts?internalAccountId='.$internal_id, [
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
             return redirect()->back()->with(array('warning' => json_encode($th->getMessage())));
        }
        if ($iban == null || $bic_swift == null || $iban == '' || $bic_swift == '' ) {
            return redirect()->back()->with(array('warning' => 'Sorry, You can not create New Bank Account succesfully because openpayd\'s internal_id is not generated by OPENPAY API. Please try again.'));
        }

        $data = new BankPoolAccount();
        $data->bank_id = $request->subbank;
        $data->currency_id = $request->currency;
        $data->iban = $iban;
        $data->swift = $bic_swift;
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
        $amountToAdd = $request->amount/getRate($currency);
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
            $response = $client->request('POST', 'https://secure-mt.openpayd.com/api/oauth/token?grant_type=client_credentials', [
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
            $response = $client->request('GET', 'https://secure-mt.openpayd.com/api/accounts?iban='.$customer_bank->iban, [
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
            $response = $client->request('POST', 'https://secure-mt.openpayd.com/api/beneficiaries', [
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
            $response = $client->request('POST', 'https://secure-mt.openpayd.com/api/beneficiaries/'.$beneficiary_id.'/bank-beneficiaries', [
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
            $response = $client->request('POST', 'https://secure-mt.openpayd.com/api/transactions/bank-payouts', [
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

           $to = $user->email;
           $subject = " You have deposited successfully.";
           $msg = "Hello ".$user->name."!\nYou have invested successfully.\nThank you.";
           $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
           sendMail($to,$subject,$msg,$headers);

        return redirect()->route('user.depositbank.create')->with('success','Deposit amount '.$request->amount.' ('.$currency->code.') successfully!');
    }
}
