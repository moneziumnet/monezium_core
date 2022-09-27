<?php

use App\Models\Admin;
use Carbon\Carbon;
use App\Models\Charge;
use App\Models\Wallet;
use App\Models\Currency;
use Illuminate\Support\Str;
use App\Models\EmailTemplate;
use App\Models\Generalsetting;
use App\Models\MerchantWallet;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon as Carbontime;
use App\Models\User;
use App\Models\Transaction;
use GuzzleHttp\Client;

if(!function_exists('getModule')){
  function getModule($value)
  {
      $admin = tenancy()->central(function ($tenant){
        if($tenant)
        {
          return Admin::where('tenant_id', $tenant->id)->first();
        }else{
            return auth()->guard('admin')->user();
        }

      });

      $sections = explode(" , ", $admin->section);
        if (in_array($value, $sections)){
            return true;
        }else{
            return false;
        }
  }
}

  if(!function_exists('showPrice')){

      function showPrice($price,$currency){
        $gs = Generalsetting::first();

        $price = round(($price) * $currency->rate,2);
        if($gs->currency_format == 0){
            return $currency->symbol. $price;
        }
        else{
            return $price. $currency->symbol;
        }
    }
  }


  if(!function_exists('admin')){
    function admin()
    {
      return auth()->guard('admin')->user();
    }
  }

  function getPhoto($filename)
  {
      if($filename){
          if(file_exists('assets/images'.'/'.$filename)) return asset('assets/images/'.$filename);
          else return asset('assets/images/default.png');
      } else{
          return asset('assets/images/default.png');
      }
  }

  function loginIp(){
    $info = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$_SERVER['REMOTE_ADDR']));
    return json_decode(json_encode($info));
}


  if(!function_exists('convertedPrice')){
    function convertedPrice($price,$currency){
      return $price = $price * $currency->rate;
    }
  }

  if(!function_exists('defaultCurr')){
    function defaultCurr(){
      return Currency::where('is_default','=',1)->first();
    }
  }

  if(!function_exists('charge')){
    function charge($slug)
    {
        $charge = Charge::where('slug',$slug)->first();
        return $charge->data;
    }
  }


  if(!function_exists('chargeCalc')){

    function chargeCalc($charge,$amount,$rate = 1)
    {
      return  ($charge->fixed_charge * $rate) + ($amount * ($charge->percent_charge/100));
    }
  }

  if(!function_exists('numFormat')){

    function numFormat($amount, $length = 0)
    {
        if(0 < $length)return number_format( $amount + 0, $length);
        return $amount + 0;
    }
  }

  if(!function_exists('amount')){

    function amount($amount,$type = 1,$length = 0){
        if($type == 2) return numFormat($amount,8);
        else return numFormat($amount,$length);
    }
  }

  if(!function_exists('dateFormat')){

    function dateFormat($date,$format = 'd M Y -- h:i a'){
      return Carbon::parse($date)->format($format);
    }
  }

  if(!function_exists('randNum')){

    function randNum($digits = 6){
      return rand(pow(10, $digits-1), pow(10, $digits)-1);
    }
  }

  if(!function_exists('str_rand')){

    function str_rand($length = 12,$up = false)
    {
        if($up) return Str::random($length);
        else return strtoupper(Str::random($length));
    }
  }

  if(!function_exists('email')){

    function email($data){
      $gs = Generalsetting::first();

              $headers = "From: $gs->sitename <$gs->from_email> \r\n";
              $headers .= "Reply-To: $gs->sitename <$gs->from_email> \r\n";
              $headers .= "MIME-Version: 1.0\r\n";
              $headers .= "Content-Type: text/html; charset=utf-8\r\n";
              mail($data['email'], $data['subject'], $data['message'], $headers);

    }
  }

  if(!function_exists('diffTime')){
    function diffTime($time)
    {
        return Carbon::parse($time)->diffForHumans();
    }
  }

  if(!function_exists('access')){
    function access($permission){
        return admin()->can($permission);
    }
  }

  if(!function_exists('mailSend')){
    function mailSend($key, array $data, $user)
    {

        $gs = GeneralSetting::first();
        $template =  EmailTemplate::where('email_type', $key)->first();

            $message = str_replace('{name}', $user->name, $template->email_body);

            foreach ($data as $key => $value) {
                $message = str_replace("{" . $key . "}", $value, $message);
            }

                $headers = "From: $gs->sitename <$gs->from_email> \r\n";
                $headers .= "Reply-To: $gs->sitename <$gs->from_email> \r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=utf-8\r\n";
                @mail($user->email, $template->email_subject, $message, $headers);


        if($gs->sms_notify){
            $message = str_replace('{name}', $user->name, $template->sms);
            foreach ($data as $key => $value) {
                $message = str_replace("{" . $key . "}", $value, $message);
            }
            sendSMS($user->phone,$message,$gs->contact_no);
        }

    }
  }

  if(!function_exists('sendSMS')) {
    function sendSMS($recipient,$message,$from){
        try {
            // Update Nexmo
            nexmo($recipient,$message,$from);
        } catch (\Throwable $th) {

        }

    }

  }

  if(!function_exists('nexmo')) {
    function nexmo(string $recipient,$message,$from){
        // Update Nexmo
        $config = array('api_key'=>'bcd5c114', 'api_secret'=>'RCpy6PaQRspb4fdi');
        $basic  = new \Vonage\Client\Credentials\Basic($config['api_key'], $config['api_secret']);
        $client = new \Vonage\Client($basic);
        $client->sms()->send(
            new \Vonage\SMS\Message\SMS($recipient, $from, $message)
        );

    }
  }

  if(!function_exists('userBalance')){
    function userBalance($user_id){
      $sql = "SELECT
                sum((`w`.`balance`/`c`.`rate`)) as `total_amount`
              FROM  `wallets` as `w`,
                    `currencies` as `c`
              WHERE `w`.`user_id`=$user_id AND
                    `w`.`user_type`=1 AND
                    `w`.`currency_id` = `c`.`id`";
        $row = DB::selectOne($sql);
        return $row;
        //return admin()->can($permission);
    }
  }

  if(!function_exists('menu')){
    function menu($route)
    {
        if(is_array($route)) {
            foreach ($route as $value) {
                if (request()->routeIs($value)) {
                    return 'active';
                }
            }
        } elseif (request()->routeIs($route)) {
            return 'active';
        }
    }
  }

  if(!function_exists('generateQR')){
    function generateQR($data)
    {
        return "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=$data&choe=UTF-8";
    }
  }

  if(!function_exists('filter')){
    function filter($key, $value)
    {
        $queries = request()->query();
        if(count($queries) > 0) $delimeter = '&';
        else  $delimeter = '?';

        if(request()->has($key)){
          $url = request()->getRequestUri();
          $pattern = "\?$key";
          $match = preg_match("/$pattern/",$url);
          if($match != 0) return  preg_replace('~(\?|&)'.$key.'[^&]*~', "\?$key=$value", $url);
          $filteredURL = preg_replace('~(\?|&)'.$key.'[^&]*~', '', $url);
          return  $filteredURL.$delimeter."$key=$value";
        }
        return  request()->getRequestUri().$delimeter."$key=$value";


    }
  }


  if(!function_exists('user_wallet_balance'))
  {
      function user_wallet_balance($auth_id, $currency_id, $wallet_type=NULL)
      {
        if(!$wallet_type)
        {
            $wallet_type = 1;
        }
          $balance = Wallet::where('user_id', $auth_id)->where('wallet_type', $wallet_type)->where('currency_id',$currency_id)->first();
          return $balance? $balance->balance: 0;
      }
  }

  if(!function_exists('user_wallet_decrement'))
  {
      function user_wallet_decrement($auth_id, $currency_id, $amount, $wallet_type=NULL)
      {
        if(!$wallet_type)
        {
            $wallet_type = 1;
        }
        $wallet = Wallet::where('user_id', $auth_id)->where('wallet_type', $wallet_type)
                  ->where('currency_id',$currency_id)->first();

        if($wallet)
        {
          $balance = Wallet::where('user_id', $auth_id)
                      ->where('currency_id',$currency_id)
                      ->where('wallet_type', $wallet_type)
                      ->decrement('balance', $amount);
          return $balance;
        }
      }
  }

  if(!function_exists('user_wallet_increment'))
  {
      function user_wallet_increment($auth_id, $currency_id, $amount, $wallet_type=NULL)
      {
        if(!$wallet_type)
        {
            $wallet_type = 1;
        }
        $gs = Generalsetting::first();
        $wallet = Wallet::where('user_id', $auth_id)->where('wallet_type', $wallet_type)
        ->where('currency_id',$currency_id)->first();
        $currency =  Currency::findOrFail($currency_id);

        if(!$wallet)
        {
            if ($currency->type == 2) {
                $user = User::findOrFail($auth_id);
                if($currency->code == 'ETH') {

                    $address = RPC_ETH('personal_newAccount',['123123']);
                    if ($address == 'error') {
                        return false;
                    }
                    $keyword = '123123';
                }
                else if ($currency->code == 'BTC') {
                    $address = RPC_BTC_Create('createwallet',[$user->email]);
                    if ($address == 'error') {
                        return false;
                    }
                    $keyword = $user->email;
                }
            }
            else {
                $address = $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999);
                $keyword = '';
            }
          $user_wallet = new Wallet();
          $user_wallet->user_id = $auth_id;
          $user_wallet->user_type = 1;
          $user_wallet->currency_id = $currency_id;
          $user_wallet->balance = $amount;
          $user_wallet->wallet_type = $wallet_type;
          $user_wallet->wallet_no =$address;
          $user_wallet->keyword =$keyword;
          $user_wallet->created_at = date('Y-m-d H:i:s');
          $user_wallet->updated_at = date('Y-m-d H:i:s');
          $user_wallet->save();

          if ($wallet_type != 9) {
              $user = User::findOrFail($auth_id);
            if ($wallet_type == 2) {
                $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->first();

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $auth_id;
                $trans->user_type   = 1;
                $trans->currency_id = 1;
                $trans->amount      = $chargefee->data->fixed_charge;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'card_issuance';
                $trans->details     = trans('Card Issuance');
                $trans->data        = '{"sender":"'.$user->name.'", "receiver":"System Account"}';
                $trans->save();
              }
              else {
                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->first();

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $auth_id;
                $trans->user_type   = 1;
                $trans->currency_id = 1;
                $trans->amount      = $chargefee->data->fixed_charge;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'wallet_create';
                $trans->details     = trans('Wallet Create');
                $trans->data        = '{"sender":"'.$user->name.'", "receiver":"System Account"}';
                $trans->save();
              }
              user_wallet_decrement($auth_id, 1, $chargefee->data->fixed_charge, 1);
              return $user_wallet->balance;
          }

        }
        else {
          $wallet->balance += $amount;
          $wallet->update();
          return $wallet->balance;
        }

      }
  }

  if(!function_exists('merchant_shop_wallet_increment'))
  {
    function merchant_shop_wallet_increment($auth_id, $currency_id, $amount, $shop_id)
    {
      $gs = Generalsetting::first();
      $user = User::findOrFail($auth_id);
      $wallet = MerchantWallet::where('merchant_id', $auth_id)->where('shop_id', $shop_id)
          ->where('currency_id',$currency_id)->first();
      $currency =  Currency::findOrFail($currency_id);
      if(!$wallet)
      {
          if ($currency->type == 2) {
              if($currency->code == 'ETH') {

                  $address = RPC_ETH('personal_newAccount',['123123']);
                  if ($address == 'error') {
                      return false;
                  }
                  $keyword = '123123';
              }
              else if ($currency->code == 'BTC') {
                  $address = RPC_BTC_Create('createwallet',[$user->email]);
                  if ($address == 'error') {
                      return false;
                  }
                  $keyword = $user->email;
              }
          }
          else {
          $address = $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999);
          $keyword = '';
          }
        $shop_wallet = new MerchantWallet();
        $shop_wallet->merchant_id = $auth_id;
        $shop_wallet->currency_id = $currency_id;
        $shop_wallet->balance = $amount;
        $shop_wallet->shop_id = $shop_id;
        $shop_wallet->wallet_no =$address;
        $shop_wallet->keyword =$keyword;
        $shop_wallet->created_at = date('Y-m-d H:i:s');
        $shop_wallet->updated_at = date('Y-m-d H:i:s');
        $shop_wallet->save();
      }
      else {
        $wallet->balance += $amount;
        $wallet->update();
        return $wallet->balance;
      }

    }
  }

  if(!function_exists('merchant_shop_wallet_decrement'))
  {
    function merchant_shop_wallet_decrement($auth_id, $currency_id, $amount, $shop_id)
    {
      $wallet = MerchantWallet::where('merchant_id', $auth_id)->where('shop_id', $shop_id)
          ->where('currency_id',$currency_id)->first();
      if($wallet)
      {
        $balance = MerchantWallet::where('merchant_id', $auth_id)
          ->where('shop_id', $shop_id)
          ->where('currency_id',$currency_id)
          ->decrement('balance', $amount);
        return $balance;
      }

    }
  }

  if(!function_exists('check_user_type'))
  {
      function check_user_type($type_id)
      {
          $explode = explode(',',auth()->user()->user_type);

          if(in_array($type_id,$explode))
          {
              return true;
          }else{
              return false;
          }
      }
  }

  if(!function_exists('check_user_type_by_id'))
  {
      function check_user_type_by_id($type_id, $user_id)
      {
          $user = User::findOrFail($user_id);
          if(!$user) {
            return false;
          }
          $explode = explode(',',$user->user_type);

          if(in_array($type_id,$explode))
          {
              return true;
          }else{
              return false;
          }
      }
  }

  if(!function_exists('check_custom_transaction_fee'))
  {
      function check_custom_transaction_fee($amount, $user, $slug)
      {
          $transaction_plan = Charge::where('slug', $slug)->where('user_id', $user->id)->get();
          $res = null;
          foreach ($transaction_plan as $value) {
            if ($value->data->from <= $amount && $value->data->till >= $amount) {
                $res = $value;
                return $res;
            }
          }
      }
  }

  if(!function_exists('check_global_transaction_fee'))
  {
      function check_global_transaction_fee($amount, $user, $slug)
      {
          $transaction_plan = Charge::where('slug', $slug)->where('plan_id', $user->bank_plan_id)->get();
          $res = null;
          foreach ($transaction_plan as $value) {
            if ($value->data->from <= $amount && $value->data->till >= $amount) {
                $res = $value;
                return $res;
            }
          }
      }
  }
  if(!function_exists('wallet_monthly_fee'))
  {
    function wallet_monthly_fee($user_id)
    {
        $now = Carbontime::now();
        $user = User::findOrFail($user_id);
        $wallets = Wallet::where('user_id', $user->id)->where('wallet_type', 1)->get();
        if($wallets)
        {
            if($user->wallet_maintenance && $now->gt($user->wallet_maintenance)) {
                $user->wallet_maintenance = Carbontime::now()->addDays(30);
                $chargefee = Charge::where('slug', 'account-maintenance')->where('plan_id', $user->bank_plan_id)->first();
                foreach ($wallets as $key => $value) {
                    # code...
                    $trans = new Transaction();
                    $trans->trnx = str_rand();
                    $trans->user_id     = $user->id;
                    $trans->user_type   = 1;
                    $trans->currency_id = $value->currency_id;
                    $trans->amount      = $chargefee->data->fixed_charge;
                    $trans->charge      = 0;
                    $trans->type        = '-';
                    $trans->remark      = 'wallet_monthly_fee';
                    $trans->details     = trans('Wallet Maintenance');
                    $trans->data        = '{"sender":"'.$user->name.'", "receiver":"System Account"}';
                    $trans->save();

                    user_wallet_decrement($user->id, $value->currency_id, $chargefee->data->fixed_charge, 1);
                    user_wallet_increment(0, $value->currency_id, $chargefee->data->fixed_charge, 9);
                    $user->update();
                }


            }
            elseif ( $user->wallet_maintenance == null) {
                $user->wallet_maintenance = Carbontime::now()->addDays(30);
                $user->update();

            }
        }
        $cards = Wallet::where('user_id', $user->id)->where('wallet_type', 2)->get();
        if($cards)
        {
            if($user->card_maintenance && $now->gt($user->card_maintenance)) {
                $user->card_maintenance = Carbontime::now()->addDays(30);
                $chargefee = Charge::where('slug', 'card-maintenance')->where('plan_id', $user->bank_plan_id)->first();

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = 1;
                $trans->amount      = $chargefee->data->fixed_charge;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'card_monthly_fee';
                $trans->details     = trans('Card Maintenance');
                $trans->data        = '{"sender":"'.$user->name.'", "receiver":"System Account"}';
                $trans->save();

                user_wallet_decrement($user->id, 1, $chargefee->data->fixed_charge, 1);
                user_wallet_increment(0, 1, $chargefee->data->fixed_charge, 9);
                $user->update();

            }
            elseif ( $user->card_maintenance == null) {
                $user->card_maintenance = Carbontime::now()->addDays(30);
                $user->update();

            }
        }
    }
  }

  if(!function_exists('str_dis'))
  {
      function str_dis($dis_string)
      {
        if (strlen($dis_string) >20 ) {
            return substr($dis_string, 0, 15).'  ...';
        }
        else {
            return $dis_string;
        }
      }
  }

  ///////////////////////////////////////////////Crypto RPC Function////////////////////////////////////////////////////////////////

  if(!function_exists('RPC_ETH'))
  {
      function RPC_ETH($method, $args, $link = 'localhost:8545')
      {
          $args = json_encode($args);
          $client = new Client();
            $headers = [
            'Content-Type' => 'application/json'
            ];
            $body = '{
            "method": "'.$method.'",
            "params": '.$args.',
            "id": 1,
            "jsonrpc": "2.0"
            }';
            try {
                $response = $client->request('POST', $link, ["headers"=>$headers, "body"=>$body]);
                $res =json_decode($response->getBody());
                return $res->result;
            } catch (\Throwable $th) {
                return 'error';
            }
      }
  }

  if(!function_exists('RPC_BTC_Create'))
  {
      function RPC_BTC_Create($method, $args, $link = 'http://127.0.0.1:18443')
      {
          $args = json_encode($args);
          $client = new Client();
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ZGV2b3BzOjEyMzEyMw=='
            ];
            $body = '{
            "method": "'.$method.'",
            "params": '.$args.',
            "id": 1,
            "jsonrpc": "2.0"
            }';
            try {
                $response = $client->request('POST', $link, ["headers"=>$headers, "body"=>$body]);
                $res =json_decode($response->getBody());
                $wallet_name =  $res->result->name;
            } catch (\Throwable $th) {
                return 'error';
            }
            try {
                $body = '{
                    "method": "getnewaddress",
                    "params": [],
                    "id": 1,
                    "jsonrpc": "2.0"
                    }';
                $response = $client->request('POST', $link.'/wallet/'.$wallet_name, ["headers"=>$headers, "body"=>$body]);
                $res =json_decode($response->getBody());
                $wallet_address =  $res->result;
            } catch (\Throwable $th) {
                return 'error';
            }
            return $wallet_address;
      }
  }

?>
