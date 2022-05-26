<?php

use App\Models\Currency;
use App\Models\Generalsetting;
use App\Models\Charge;


  if(!function_exists('showPrice')){
      
      function showPrice($price,$currency){
        $gs = Generalsetting::first();
        
        $price = round(($price) * $currency->value,2);
        if($gs->currency_format == 0){
            return $currency->sign. $price;
        }
        else{
            return $price. $currency->sign;
        }
    }
  }
  

  if(!function_exists('convertedPrice')){
    function convertedPrice($price,$currency){
      return $price = $price * $currency->value;
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


?>
