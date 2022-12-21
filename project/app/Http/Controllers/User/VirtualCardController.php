<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Generalsetting;
use App\Models\User;
use App\Models\Currency;
use App\Models\Wallet;
use App\Models\Charge;
use App\Models\VirtualCard;
use App\Models\Transaction;
use App\Models\BankGateway;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Datatables;
use GuzzleHttp\Client;

class VirtualCardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(){
        $data['virtualcards'] = VirtualCard::where('user_id',auth()->id())->get();
        $exist_currencies = VirtualCard::where('user_id',auth()->id())->pluck('currency_id');
        $data['currencylist'] = Currency::wherestatus(1)->whereNotIn('id', $exist_currencies->toArray())->where('type', 1)->get();

        return view('user.virtualcard.index', $data);
    }

    public function store(Request $request) {

        $user=auth()->user();
        $currency=Currency::where('id', $request->currency_id)->first();
        if($currency->code != 'EUR') {
            return back()->with('error', 'This api support only for EUR currency.');
        }

        $v_card = VirtualCard::where('user_id', auth()->user()->id)->where('currency_id', $request->currency_id)->first();
        if($v_card) {
            return back()->with('error', $currency->code . ' Virtual Card already exists.');
        }
        $bankgateways = BankGateway::where('keyword', 'swan')->get();
        if(count($bankgateways) > 1) {
            foreach ($bankgateways as $key => $value) {
                $bankaccount = BankAccount::where('user_id', $user->id)->where('currency_id', $request->currency_id)->where('subbank_id', $value->subbank_id)->first();
                if($bankaccount) {
                    $account = $bankaccount;
                    $bankgateway = $value;
                }
            }
        }
        else {
            return back()->with('error', ' The api does not exist for creating Virtual Card.');
        }
        if(!$account) {
            return back()->with('error', 'Please create swan Bank account before creating virtual card.');
        }
        $client = New Client();
        try {
            $options = [
              'multipart' => [
                [
                  'name' => 'client_id',
                  'contents' => $bankgateway->information->client_id
                ],
                [
                  'name' => 'client_secret',
                  'contents' => $bankgateway->information->client_secret
                ],
                [
                  'name' => 'grant_type',
                  'contents' => 'client_credentials'
                ]
            ]];
          $response = $client->request('POST', 'https://oauth.swan.io/oauth2/token', $options);
          $res_body = json_decode($response->getBody());
          $access_token = $res_body->access_token;
        } catch (\Throwable $th) {
            return redirect()->back()->with(array('warning' => json_encode($th->getMessage())));
        }
        try {
            $body = '{"query":"query MyQuery {\\n  accounts {\\n    edges {\\n      node {\\n        BIC\\n        IBAN\\n        memberships {\\n          edges {\\n            node {\\n              email\\n              id\\n            }\\n          }\\n        }\\n      }\\n    }\\n  }\\n}\\n","variables":{}}';
            $headers = [
                'Authorization' => 'Bearer '.$access_token,
                'Content-Type' => 'application/json'
            ];
            $response = $client->request('POST', 'https://api.swan.io/sandbox-partner/graphql', [
                'body' => $body,
                'headers' => $headers
            ]);
            $res_body = json_decode($response->getBody());
            $accountlist = $res_body->data->accounts->edges;
            if(count($accountlist) > 0) {
                foreach ($accountlist as $key => $value) {
                    if($value->node->IBAN == $account->iban) {
                        $membership_id = $value->node->memberships->edges[0]->node->id;
                    }
                }
            }

        } catch (\Throwable $th) {
            return redirect()->back()->with(array('warning' => json_encode($th->getMessage())));
        }
        if(!$membership_id){
            return back()->with('error', 'The membership id for your swan bank account does not exist');
        }
        try {
            $redirect_url = route('user.card.index');
            $body = '{"query":"\\nmutation MyMutation {\\n  addCard(\\n    input: {\\n      accountMembershipId: \\"'.$membership_id.'\\"\\n      withdrawal: true\\n      international: true\\n      nonMainCurrencyTransactions: true\\n      eCommerce: true\\n      consentRedirectUrl: \\"'.$redirect_url.'\\"\\n    }\\n  ) {\\n    ... on AddCardSuccessPayload {\\n      __typename\\n      card {\\n        statusInfo {\\n          ... on CardConsentPendingStatusInfo {\\n            __typename\\n            consent {\\n              consentUrl\\n            }\\n          }\\n        }\\n        id\\n      }\\n    }\\n  }\\n}\\n","variables":{}}';
            $headers = [
                'Authorization' => 'Bearer '.$access_token,
                'Content-Type' => 'application/json'
            ];
            $response = $client->request('POST', 'https://api.swan.io/sandbox-partner/graphql', [
                'body' => $body,
                'headers' => $headers
            ]);
            $res_body = json_decode($response->getBody());

            if (isset($res_body->data, $res_body->data->addCard, $res_body->data->addCard->card)) {
                return redirect()->away($res_body->data->addCard->card->consentUrl);
            }
            return redirect()->back()->with(array('warning' => "Can't create a Card, becouse this gateway is not on live."));

        } catch (\Throwable $th) {
            return redirect()->back()->with(array('warning' => json_encode($th->getMessage())));
        }
        $user_wallet =  Wallet::where('user_id', auth()->user()->id)->where('wallet_type', 2)->where('currency_id', $request->currency_id)->first();
        if(!$user_wallet){
            $gs = Generalsetting::first();
            $user_wallet = new Wallet();
            $user_wallet->user_id = auth()->user()->id;
            $user_wallet->user_type = 1;
            $user_wallet->currency_id = $request->currency_id;
            $user_wallet->balance = 0;
            $user_wallet->wallet_type = 2;
            $user_wallet->wallet_no =$gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999);
            $user_wallet->created_at = date('Y-m-d H:i:s');
            $user_wallet->updated_at = date('Y-m-d H:i:s');
            $user_wallet->save();
            $user =  User::findOrFail(auth()->id());
            $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
            if(!$chargefee){
                $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
            }


            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $user->id;
            $trans->user_type   = 1;
            $trans->currency_id = defaultCurr();
            $trans->amount      = $chargefee->data->fixed_charge;

            $trans_wallet = get_wallet($user->id, defaultCurr(), 1);

            $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
            $trans->charge      = 0;
            $trans->type        = '-';
            $trans->remark      = 'card_issuance';
            $trans->details     = trans('Card Issuance');
            $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"System Account"}';
            $trans->save();

            user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
            user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
        }


        $coin=$currency->code;
        $trx='VC-'.Str::random(6);
        $name=$request->first_name." ".$request->last_name;
        $ds = "";

        $sav['user_id']=$user->id;
        $sav['first_name']=$request->first_name ?? explode(" ", $user->name)[0];
        $sav['last_name']=$request->last_name ?? explode(" ", $user->name)[1];
        $sav['account_id']=$user->id;
        $sav['card_hash']=$user->id;
        $sav['card_pan']=generate_card_number(16);
        $sav['masked_card']='mc_'.rand(100, 999);
        $sav['cvv']=rand(100, 999);
        $sav['expiration']='10/24';
        $sav['card_type']='normal';
        $sav['name_on_card']='noc_US';
        $sav['callback']=" ";
        $sav['ref_id']=$trx;
        $sav['secret']=$trx;
        $sav['city']=$user->city;
        $sav['zip_code']=$user->zip;
        $sav['address']=$user->address;
        $sav['wallet_id']=$user_wallet->id;
        $sav['amount']=0;
        $sav['currency_id']=$request->currency_id;
        $sav['charge']=0;
        VirtualCard::create($sav);
        return back()->with('success', 'Virtual card was successfully created');
    }

    public function transaction($id) {
        $v_card=VirtualCard::whereid($id)->first();
        // $curl = curl_init();
        // curl_setopt_array($curl, array(
        // CURLOPT_URL => "https://api.flutterwave.com/v3/virtual-cards/".$val->card_hash."/transactions?from=".date('Y-m-d', strtotime($val['created_at']))."&to=".Carbon::tomorrow()->format('Y-m-d')."&index=1&size=100",
        // CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_ENCODING => "",
        // CURLOPT_MAXREDIRS => 10,
        // CURLOPT_TIMEOUT => 0,
        // CURLOPT_FOLLOWLOCATION => true,
        // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        // CURLOPT_CUSTOMREQUEST => "GET",
        // CURLOPT_HTTPHEADER => array(
        //     "Content-Type: application/json",
        //     "Authorization: Bearer ".env('SECRET_KEY')
        // ),
        // ));
        // $response = curl_exec($curl);
        // curl_close($curl);
        // $data['transactions']=$response;
        $data['transactions'] = Transaction::where('wallet_id',$v_card->wallet_id)->orderBy('id','desc')->paginate(15);
        return view('user.virtualcard.transaction', $data);
    }

    public function detail(Request $request) {
        $card = VirtualCard::findOrFail($request->card_id);
        $card->wallet_no = $card->wallet->wallet_no;
        return response()->json($card);
    }

    public function withdraw(Request $request) {
        $card = VirtualCard::findOrFail($request->withdraw_id);
        $fromWallet = Wallet::findOrFail($card->wallet_id);
        $user = User::find(auth()->user()->id);

        if(!isset($request->amount)) {
            return back()->with('error','Please input amount');
        }

        $gs = Generalsetting::first();


        $toWallet = Wallet::where('currency_id', $fromWallet->currency_id)->where('user_id',$user->id)->where('wallet_type',1)->where('user_type',1)->first();
        $currency =  Currency::findOrFail($fromWallet->currency_id);
        if ($currency->type == 2) {
            $keyword = str_rand(6);
            $address = RPC_ETH('personal_newAccount',[$keyword]);
            if ($address == 'error') {
                return back()->with('error','You can not create this wallet because there is some issue in crypto node.');
            }
        }
        else {
            $address = $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999);
            $keyword = '';
        }
        if(!$toWallet){
            $toWallet = Wallet::create([
                'user_id'     => $user->id,
                'user_type'   => 1,
                'currency_id' => $fromWallet->currency_id,
                'balance'     => 0,
                'wallet_type' => $request->wallet_type,
                'wallet_no' => $address,
                'keyword' => $keyword
            ]);
            if($request->wallet_type == 2) {
                $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                if(!$chargefee) {
                    $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                }


                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = defaultCurr();
                $trans->amount      = $chargefee->data->fixed_charge;

                $trans_wallet = get_wallet($user->id, defaultCurr(), 1);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'card_issuance';
                $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"System Account"}';
                $trans->details     = trans('Card Issuance');
                $trans->save();
            }
            else {
                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                if(!$chargefee) {
                    $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                }

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = defaultCurr();

                $trans_wallet = get_wallet($user->id, defaultCurr(), 1);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                $trans->amount      = $chargefee->data->fixed_charge;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'wallet_create';
                $trans->details     = trans('Wallet Create');
                $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"System Account"}';
                $trans->save();
            }
            user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
            user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
        }

        if($fromWallet->balance < $request->amount){
            return back()->with('error','Insufficient balance to your '.$fromWallet->currency->code.' wallet');
        }

        $fromWallet->balance -=  $request->amount;
        $fromWallet->update();

        $toWallet->balance += $request->amount;
        $toWallet->update();


        $trnx              = new Transaction();
        $trnx->trnx        = str_rand();
        $trnx->user_id     = $user->id;
        $trnx->user_type   = 1;
        $trnx->currency_id = $fromWallet->currency->id;
        $trnx->wallet_id   = $fromWallet->id;
        $trnx->amount      = $request->amount ;
        $trnx->charge      = 0;
        $trnx->remark      = 'Own_transfer';
        $trnx->type        = '-';
        $trnx->details     = trans('Transfer  '.$fromWallet->currency->code.'money other wallet');
        $trnx->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.($user->company_name ?? $user->name).'"}';
        $trnx->save();

        $toTrnx              = new Transaction();
        $toTrnx->trnx        = $trnx->trnx;
        $toTrnx->user_id     = $user->id;
        $toTrnx->user_type   = 1;
        $toTrnx->currency_id = $toWallet->currency->id;
        $toTrnx->wallet_id   = $toWallet->id;
        $toTrnx->amount      = $request->amount;
        $toTrnx->charge      = 0;
        $toTrnx->remark      = 'Own_transfer';
        $toTrnx->type          = '+';
        $toTrnx->details     = trans('Transfer  '.$fromWallet->currency->code.'money other wallet');
        $toTrnx->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.($user->company_name ?? $user->name).'"}';
        $toTrnx->save();

        return back()->with('message','Money exchanged successfully.');
    }
}

