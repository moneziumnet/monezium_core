<?php

use App\Models\Currency;
use App\Models\Generalsetting;



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


?>
