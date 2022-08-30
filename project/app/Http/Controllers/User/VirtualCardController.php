<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Generalsetting;
use App\Models\User;
use App\Models\Currency;
use App\Models\VirtualCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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
            $sav['amount']=$request->amount;
            $sav['charge']=0;
            VirtualCard::create($sav);
            return back()->with('success', 'Virtual card was successfully created');
        }else{
            return back()->with('error', $result['message']);
        }
    }

    public function transaction($id) {
        $val=Virtual::whereid($id)->first();
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


}

