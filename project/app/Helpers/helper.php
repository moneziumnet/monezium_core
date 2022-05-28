<?php

use Carbon\Carbon;
use App\Models\Currency;
use App\Models\Generalsetting;
use App\Models\Charge;
use Illuminate\Support\Str;
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



?>
