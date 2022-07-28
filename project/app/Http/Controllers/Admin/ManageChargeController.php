<?php

namespace App\Http\Controllers\Admin;

use App\Models\Charge;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Datatables;

class ManageChargeController extends Controller
{

    public function datatables($id)
    {
         $datas = Charge::where('plan_id', $id)->get();

         return Datatables::of($datas)
                            ->editColumn('name', function(Charge $data) {
                                return $data->name;
                            })
                            ->editColumn('percent', function(Charge $data)  {
                                    return $data->data->percent_charge;
                            })
                            ->editColumn('fixed', function(Charge $data) {
                                    return $data->data->fixed_charge;
                            })
                            ->editColumn('from', function(Charge $data)  {
                                if (isset($data->data->from)) {
                                    return  $data->data->from;
                                }
                                else {
                                    return 0;
                                }
                            })
                            ->editColumn('till', function(Charge $data)  {
                                if (isset($data->data->till)) {
                                    return  $data->data->till;
                                }
                                else {
                                    return 0;
                                }
                            })
                            ->addColumn('action', function (Charge $data)  {
                                        return '<a href="'.route('admin.edit.charge',$data->id).'" class="btn btn-primary btn-block"><i class="fas fa-edit"></i>'. __('Edit Charge').'</a>';
                            })

                            ->rawColumns(['action'])
                            ->toJson();
        }


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

    public function createCharge(Request $request)
    {
        $data = new Charge();
        if ($request->user_id == 0)
        {
            $total = Charge::where('slug', strtolower($request->name))->where('plan_id',$request->plan_id )->get()->count() + 1;
            $data->name = $request->name." ".$total;
            $data->slug = strtolower($request->name);
        }
        else {
            $data->name = $request->name;
            $data->slug = $request->slug;
        }
        $data->user_id = $request->user_id;
        $data->plan_id = $request->plan_id;
        $inputs = $request->except(array('_token','name','user_id', 'plan_id', 'slug' ));
        foreach($inputs as $key =>  $input){
            $rules[$key] = 'required|numeric|min:0';
        }
        $request->validate($rules);
        $data->data = $inputs;

        $data->save();
        return redirect()->back()->with(array('message' => 'Customer Plan Create Successfully'));
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
        return back()->with('message',$charge->name.' Plan Charge Updated');
    }
}
