<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Str;
use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Models\DepositBank;
use App\Models\Currency;
use App\Models\BankGateway;
use App\Models\PlanDetail;
use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\SubInsBank;
use Auth;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;


class DepositBankController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $data['deposits'] = DepositBank::orderby('id','desc')->whereUserId(auth()->id())->paginate(10);
        return view('user.depositbank.index',$data);
    }

    public function create(){
        $data['banks'] = SubInsBank::get();
        return view('user.depositbank.create',$data);
    }

    public function store(Request $request){

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

        $txnid = Str::random(4).time();
        $deposit = new DepositBank();
        $deposit['deposit_number'] = Str::random(12);
        $deposit['user_id'] = auth()->id();
        $deposit['currency_id'] = $request->currency_id;
        $deposit['amount'] = $amountToAdd;
        $deposit['method'] = $request->method;
        $deposit['sub_bank_id'] = $request->bank;
        $deposit['txnid'] = $request->txnid;
        $deposit['status'] = "pending";
        $deposit->save();

        $client = new Client();
        $bankgateway = BankGateway::where('subbank_id', $request->bank)->first();
        $subbank = SubInsBank::where('id', $request->bank)->first();
        $subuser = Admin::where('id', $subbank->ins_id)->first();
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
        $response = $client->request('POST', 'https://play.railsbank.com/v1/customer/ledgers', [
            'body' => '{
                "holder_id": "'.$enduser.'",
                "partner_product": "ExampleBank-USD-1",
                "asset_class": "currency",
                "asset_type": "usd",
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
        $response = $client->request('POST', 'https://play.railsbank.com/v1/customer/ledgers/'.$ledger.'/external-accounts', [
            'body' => '{
                "number": "'.$request->banknumber.'",
                "bank_code": "'.$request->bankcode.'",
                "name": "'.$user->name.'",
                "account_type": "routing-number",
                "bank_name": "'.$request->bankname.'",
                "type": "",
                "legal_type": "person",
                "external_account_meta": {}
              }'
            ,
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
                'Content-Type' => 'application/json',
                ],
        ]);
        $external_account = json_decode($response->getBody())->external_account_id;

        $response = $client->request('POST','https://play.railsbank.com/v1/customer/beneficiaries', [
            'body' => '{
                holder_id: '.$enduser.',
                asset_class: "currency",
                asset_type: "usd",
                iban: "'.$subbank->iban.'",
                bic_swift: "'.$subbank->swift.'",
                person: {
                  name: "'.$subuser->name.'",
                  address: { address_iso_country: "US" },
                  email: "'.$request->email.'"
                }
              }',
            'headers' => [
                'Accept'=> 'application/json',
                'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
                'Content-Type' => 'application/json',
            ],
        ]);
        $beneficiary = json_decode($response->getBody())->beneficiary_id;

        $response = $client->request('POST', 'https://play.railsbank.com/v1/customer/transactions', [
            'body' => '{
                "ledger_from_id": "'.$ledger.'",
                "beneficiary_id": "'.$beneficiary.'",
                "payment_type": "payment-type-Global-SWIFT",
                "amount": "'.$request->amount.'"
              }',
            'headers' => [
               'Accept'=> 'application/json',
              'Authorization' => 'API-Key '.$bankgateway->information->API_Key,
              'Content-Type' => 'application/json',
            ],
          ]);
        $transaction = json_decode($response->getBody())->transaction_id;



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
