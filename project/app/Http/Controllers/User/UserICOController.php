<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\IcoToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class UserICOController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $data['ico_tokens'] = IcoToken::all();//where('user_id',auth()->id())->get();
        return view('user.ico.index', $data);
    }

    public function mytoken()
    {
        $data['ico_tokens'] = IcoToken::where('user_id',auth()->id())->get();
        return view('user.ico.mytoken', $data);
    }

    public function edit($id)
    {
        $data['data'] = IcoToken::findOrFail($id);
        return view('user.ico.edit', $data);
    }

    public function details($id) 
    {
        $data['item'] = IcoToken::findOrFail($id);
        return view('user.ico.detail', $data);
    }

    public function delete($id)
    {
        $data = IcoToken::findOrFail($id);
        $currency = Currency::findOrFail($data->currency_id);
        File::delete('assets/doc/'.$data->white_paper);
        $currency->delete();
        $data->delete();
        return  redirect()->back()->with('message','ICO Token has been deleted successfully');
    }

    public function store(Request $request) 
    {
        $rules = [
            'whitepaper' => 'required|mimes:doc,docx'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with('error',$validator->getMessageBag()->toArray()['whitepaper'][0]);
        }

        $data = new IcoToken();
        if ($file = $request->file('whitepaper'))
        {
            $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/doc',$name);
        }
        $currency = Currency::where('code', $request->code)
            ->orWhere('symbol', $request->symbol)    
            ->first();
        if($currency) {
            return redirect()->back()->with('error','The currency already exists.');
        } else {
            $currency = new Currency();
            $currency->symbol  = $request->symbol;
            $currency->code  = $request->code;
            $currency->curr_name = $request->name;
            $currency->rate = $request->price == 0 ? 0 : 1 / $request->price;
            $currency->type  = 2;
            $currency->save();
        }
        $data->name = $request->name;
        $data->user_id = auth()->id();
        $data->price = $request->price;
        $data->total_supply = $request->total_supply;
        $data->end_date = $request->end_date;
        $data->currency_id = $currency->id;
        $data->white_paper = $name;
        $data->save();

        return redirect()->back()->with('message','New ICO Token has been created successfully');
    }

    public function update($id, Request $request) 
    {
        $rules = [
            'whitepaper' => 'mimes:doc,docx'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with('error',$validator->getMessageBag()->toArray()['whitepaper'][0]);
        }

        $data = IcoToken::find($id);
        if ($file = $request->file('whitepaper'))
        {
            $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/doc',$name);
            $data->white_paper = $name;
        }
        $currency = Currency::where('code', $request->code)
            ->orWhere('symbol', $request->symbol)    
            ->first();
        if($currency->id != $data->currency_id && $currency) {
            return redirect()->back()->with('error','The currency already exists.');
        } 
        if(!$currency || $currency && $currency->id == $data->currency_id) {
            if(!$currency)
                $currency = new Currency();
            $currency->symbol  = $request->symbol;
            $currency->code  = $request->code;
            $currency->curr_name = $request->name;
            $currency->rate = $request->price == 0 ? 0 : 1 / $request->price;
            $currency->type  = 2;
            $currency->save();
            $data->currency_id = $currency->id;
        }
        $data->name = $request->name;
        $data->user_id = auth()->id();
        $data->price = $request->price;
        $data->total_supply = $request->total_supply;
        $data->end_date = $request->end_date;
        $data->save();

        return redirect(route('user.ico.mytoken'))->with('message','The ICO Token has been updated successfully');
    }
}