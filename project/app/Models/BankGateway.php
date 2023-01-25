<?php

namespace App\Models;
use Illuminate\{
    Database\Eloquent\Model
};


class BankGateway extends Model
{
    protected $casts = ['information'=>'encrypted:object'];
    // protected $casts = ['information'=>'object'];

    public function currency()
    {
        return $this->belongsTo('App\Models\Currency')->withDefault();
    }

    public function subinsbank()
    {
        return $this->belongsTo(SubInsBank::class,'subbank_id')->withDefault();
    }

    public static function scopeHasGateway($curr)
    {
        return BankGateway::where('currency_id', 'like', "%\"{$curr}\"%")->get();
    }

    public function convertAutoData(){
        return  json_decode($this->information,true);
    }

    public function getAutoDataText(){
        $text = $this->convertAutoData();
        return end($text);
    }

    public function showKeyword(){
        $data = $this->keyword == null ? 'other' : $this->keyword;
        return $data;
    }

}
