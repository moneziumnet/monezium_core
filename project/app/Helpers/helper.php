<?php

use Carbon\Carbon;
use App\Models\Currency;
use App\Models\Generalsetting;
use App\Models\Charge;


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


?>
