<?php

use Carbon\Carbon;
use App\Models\Charge;
use App\Models\Wallet;
use App\Models\Currency;
use Illuminate\Support\Str;
use App\Models\EmailTemplate;
use App\Models\Generalsetting;
use PHPMailer\PHPMailer\PHPMailer;


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

  function merchant()
  {
    return auth()->guard('merchant')->user();
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
  
      if ($gs->email_notify) {
          if ($gs->mail_type == 'php_mail') {
              $headers = "From: $gs->sitename <$gs->email_from> \r\n";
              $headers .= "Reply-To: $gs->sitename <$gs->email_from> \r\n";
              $headers .= "MIME-Version: 1.0\r\n";
              $headers .= "Content-Type: text/html; charset=utf-8\r\n";
              @mail($data['email'], $data['subject'], $data['message'], $headers);
          }
          else {
              $mail = new PHPMailer(true);
      
              try {
                  // $mail->isSMTP();
                  $mail->Host       = $gs->smtp_host;
                  $mail->SMTPAuth   = true;
                  $mail->Username   = $gs->smtp_user;
                  $mail->Password   = $gs->smtp_pass;
                  if ($gs->mail_encryption == 'ssl') {
                      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                  } else {
                      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                  }
                  $mail->Port       = $gs->smtp_port;
                  $mail->CharSet = 'UTF-8';
                  $mail->setFrom($gs->from_email, $gs->from_name);
                  $mail->addAddress($data['email'], $data['name']);
                  $mail->addReplyTo($gs->from_email, $gs->from_name);
                  $mail->isHTML(true);
                  $mail->Subject = $data['subject'];
                  $mail->Body    = $data['message'];
                  $mail->send();
              } catch (Exception $e) {
                  throw new Exception($e);
              }
          }
      }
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

        if($gs->email_notify){
            $message = str_replace('{name}', $user->name, $template->email_body);
        
            foreach ($data as $key => $value) {
                $message = str_replace("{" . $key . "}", $value, $message);
            }
        
            if ($gs->mail_type == 'php_mail') {
                $headers = "From: $gs->sitename <$gs->email_from> \r\n";
                $headers .= "Reply-To: $gs->sitename <$gs->email_from> \r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=utf-8\r\n";
                @mail($user->email, $template->email_subject, $message, $headers);
            } else {
                $mail = new PHPMailer(true);
        
                try {
                    $mail->isSMTP();
                    $mail->Host       = $gs->smtp_host;
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $gs->smtp_user;
                    $mail->Password   = $gs->smtp_pass;
                    if ($gs->mail_encryption == 'ssl') {
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    } else {
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    }
                    $mail->Port       = $gs->smtp_port;
                    $mail->CharSet = 'UTF-8';
                    $mail->setFrom($gs->from_email, $gs->from_name);
                    $mail->addAddress($user->email, $user->name);
                    $mail->addReplyTo($gs->from_email, $gs->from_name);
                    $mail->isHTML(true);
                    $mail->Subject = $template->email_subject;
                    $mail->Body    = $message;
                    $mail->send();
                } catch (Exception $e) {
                  // throw new Exception($e);
                }
            }
        }

        if($gs->sms_notify){
            $message = str_replace('{name}', $user->name, $template->sms);
            foreach ($data as $key => $value) {
                $message = str_replace("{" . $key . "}", $value, $message);
            }
            sendSMS($user->phone,$message,$gs->contact_no);
        }
        
    }
  }
  
  if(!function_exists('userBalance')){
    function userBalance($user_id){
      $sql = "SELECT 
                sum((`w`.`balance`*`c`.`rate`)) as `total_amount` 
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
      function user_wallet_balance($auth_id, $currency_id)
      {
          $balance = Wallet::where('user_id', $auth_id)->where('currency_id',$currency_id)->first();
          return $balance? $balance->balance: 0;
      }
  }
  
  if(!function_exists('user_wallet_decrement'))
  {
      function user_wallet_decrement($auth_id, $currency_id, $amount)
      {
        $wallet = Wallet::where('user_id', $auth_id)
                  ->where('currency_id',$currency_id)->first();

        if($wallet && $wallet->balance >= $amount)
        {
          $balance = Wallet::where('user_id', $auth_id)
                      ->where('currency_id',$currency_id)
                      ->decrement('balance', $amount);
          return $balance;
        }
      }
  }
  
  if(!function_exists('user_wallet_increment'))
  {
      function user_wallet_increment($auth_id, $currency_id, $amount)
      {
        $wallet = Wallet::where('user_id', $auth_id)
            ->where('currency_id',$currency_id)->first();
        
        if(!$wallet)
        {
          $user_wallet = new Wallet();
          $user_wallet->user_id = $auth_id;
          $user_wallet->user_type = 1;
          $user_wallet->currency_id = $currency_id;
          $user_wallet->balance = $amount;
          $user_wallet->created_at = date('Y-m-d H:i:s');
          $user_wallet->updated_at = date('Y-m-d H:i:s');
          $user_wallet->save();
          return $user_wallet->balance;
        }
        else {
          $wallet->balance += $amount;
          $wallet->update();
          return $wallet->balance;
        }

      }
  }




?>
