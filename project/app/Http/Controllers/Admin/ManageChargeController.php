<?php

namespace App\Http\Controllers\Admin;

use App\Models\Charge;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManageChargeController extends Controller
{
    public function index(Request $request, $id)
    {
        $search  = $request->search;
        $charges = Charge::when($search,function($q) use($search){
            return $q->where('name','like',"%$search%");
        })->where('plan_id', $id)->get();
        $data['search'] = $search;
        $data['charges'] = $charges;
        $data['plan_id'] = $id;
        return view('admin.charge.index', $data);
    }

    public function editCharge($id)
    {
        $charge = Charge::findOrFail($id);
        return view('admin.charge.edit',compact('charge'));
    }

    public function updateCharge(Request $request,$id)
    {
        if($request->fixed_charge < $request->percent_charge){
            return back()->with('error','Percent charge amount can not be greater than fixed charge amount.');
        }
        if($request->minimum && $request->minimum <= $request->fixed_charge){
            return back()->with('error','Fixed charge should be less than minimum amount.');
        }
        $rules  = [];
        $charge =  Charge::findOrFail($id);
        $inputs = $request->except('_token');
        foreach($inputs as $key =>  $input){
            $rules[$key] = 'required|numeric|min:0';
        }
        $request->validate($rules);
        $charge->data = $inputs;
        $charge->update();
        return back()->with('success',$charge->name.' Charge Updated');
    }
}
