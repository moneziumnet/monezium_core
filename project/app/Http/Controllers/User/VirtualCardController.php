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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Datatables;

class VirtualCardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(){
        $data['virtualcards'] = VirtualCard::where('user_id',auth()->id())->get();
        $data['currencylist'] = Currency::wherestatus(1)->where('type', 1)->get();
        return view('user.virtualcard.index', $data);
    }

    // public function create() {
    //     return view('user.virtualcard.index', $data);

    // }
    public function store(Request $request) {

        $user=auth()->user();
        $currency=Currency::where('id', $request->currency_id)->first();

        $check =  Wallet::where('user_id', $request->user_id)->where('wallet_type', 1)->where('currency_id', $request->currency_id)->first();
        if($check){
            return back()->with('error', 'This wallet already exist');
        }
        $gs = Generalsetting::first();
        $user_wallet = new Wallet();
        $user_wallet->user_id = auth()->id();
        $user_wallet->user_type = 1;
        $user_wallet->currency_id = $request->currency_id;
        $user_wallet->balance = 0;
        $user_wallet->wallet_type = 2;
        $user_wallet->wallet_no =$gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999);
        $user_wallet->created_at = date('Y-m-d H:i:s');
        $user_wallet->updated_at = date('Y-m-d H:i:s');
        $user_wallet->save();

        $user =  User::findOrFail(auth()->id());
        $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->first();

        $trans = new Transaction();
        $trans->trnx = str_rand();
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id = 1;
        $trans->amount      = $chargefee->data->fixed_charge;

        $trans_wallet = get_wallet($user->id, 1, 1);

        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
        $trans->charge      = 0;
        $trans->type        = '-';
        $trans->remark      = 'card_issuance';
        $trans->details     = trans('Card Issuance');
        $trans->data        = '{"sender":"'.$user->name.'", "receiver":"System Account"}';
        $trans->save();

        user_wallet_decrement($user->id, 1, $chargefee->data->fixed_charge, 1);
        user_wallet_increment(0, 1, $chargefee->data->fixed_charge, 9);

        $coin=$currency->code;
        $trx='VC-'.Str::random(6);
        $name=$request->first_name." ".$request->last_name;
        $ds = "";
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.flutterwave.com/v3/virtual-cards",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS =>"{\n    \"currency\": \"$coin\",\n    \"amount\": $request->amount,\n    \"billing_name\": \"$name\",\n   \"callback_url\": \"$ds/\"\n}",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Authorization: Bearer ".env('SECRET_KEY')
        ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response, true);

        if (array_key_exists('data', $result) && ($result['status'] === 'success')) {

            //Save Card
            $sav['user_id']=$user->id;
            $sav['first_name']=$request->first_name;
            $sav['last_name']=$request->last_name;
            $sav['account_id']=$result['data']['account_id'];
            $sav['card_hash']=$result['data']['id'];
            $sav['card_pan']=$result['data']['card_pan'];
            $sav['masked_card']=$result['data']['masked_pan'];
            $sav['cvv']=$result['data']['cvv'];
            $sav['expiration']=$result['data']['expiration'];
            $sav['card_type']=$result['data']['card_type'];
            $sav['name_on_card']=$result['data']['name_on_card'];
            $sav['callback']=" ";
            $sav['ref_id']=$trx;
            $sav['secret']=$trx;
            $sav['city']=$result['data']['city'];
            $sav['state']=$result['data']['state'];
            $sav['zip_code']=$result['data']['zip_code'];
            $sav['address']=$result['data']['address_1'];
            $sav['amount']=0;
            $sav['currency_id']=$request->currency_id;
            $sav['charge']=0;
            VirtualCard::create($sav);
            return back()->with('success', 'Virtual card was successfully created');
        }else{
            $sav['user_id']=$user->id;
            $sav['first_name']=$request->first_name ?? explode(" ", $user->name)[0];
            $sav['last_name']=$request->last_name ?? explode(" ", $user->name)[1];
            $sav['account_id']=$user->id;
            $sav['card_hash']=$user->id;
            $sav['card_pan']='cp_'.rand(1000, 9999);
            $sav['masked_card']='mc_'.rand(100, 999);
            $sav['cvv']=rand(1000, 9999);
            $sav['expiration']='10/24';
            $sav['card_type']='normal';
            $sav['name_on_card']='noc_US';
            $sav['callback']=" ";
            $sav['ref_id']=$trx;
            $sav['secret']=$trx;
            $sav['city']=$user->city;
            $sav['zip_code']=$user->zip;
            $sav['address']=$user->address;
            $sav['amount']=0;
            $sav['currency_id']=$request->currency_id;
            $sav['charge']=0;
            VirtualCard::create($sav);
            return back()->with('success', 'Virtual card was successfully created');
            // return back()->with('error', $result['message']);
        }
    }

    public function transaction($id) {
        $val=VirtualCard::whereid($id)->first();
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.flutterwave.com/v3/virtual-cards/".$val->card_hash."/transactions?from=".date('Y-m-d', strtotime($val['created_at']))."&to=".Carbon::tomorrow()->format('Y-m-d')."&index=1&size=100",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Authorization: Bearer ".env('SECRET_KEY')
        ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $data['transactions']=$response;
        return view('user.virtualcard.transaction', $data);
    }

    public function withdraw(Request $request) {
        $user=auth()->user();
        $vcard=VirtualCard::where('card_hash',$request->id)->first();
        if($user->balance>$request->amount){
            $coin='USD';
            $trx='VC-'.Str::random(6);
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.flutterwave.com/v3/virtual-cards/".$vcard->card_hash."/withdraw",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>"{\n    \"debit_currency\": \"$coin\",\n    \"amount\": $request->amount\n}",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer ".env('SECRET_KEY')
            ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $result = json_decode($response, true);
            if (array_key_exists('data', $result) && ($result['status'] === 'success')) {

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = 1;
                $trans->amount      = $request->amount;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'virtual_card_withdrawal';
                $trans->details     = trans('Virtual Card Withdrawal');
                $trans->data        = '{"sender":"'.$user->name.'", "receiver":"Other Bank System"}';
                $trans->save();

                //Debit User
                $vcard->amount=$vcard->amount-$request->amount;
                $vcard->save();
                return redirect()->route('user.card.transaction', ['id'=>$vcard->id])->with('success', $result['message']);
            }else{
                return back()->with('alert', $result['message']);
            }
        }else{
            return back()->with('alert', 'Account balance is insufficient');
        }
    }


}

