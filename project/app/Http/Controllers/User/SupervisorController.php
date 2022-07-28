<?php

namespace App\Http\Controllers\User;

use Auth;
use Datatables;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BankPlan;
use App\Models\Charge;
use Illuminate\Support\Facades\Validator;

class SupervisorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

    }

    public function index()
    {
        $data = auth()->user();
        $plans = BankPlan::where('id','!=',$data->bank_plan_id)->get();
        $plan = BankPlan::findOrFail($data->bank_plan_id);
        //dd($plan);
        // $data['globals'] = Charge::where('plan_id', $user->bank_plan_id)->get();
        $data['data'] = $data;
        $data['plan'] = $plan;
        $data['plans'] = $plans;
        return view('user.supervisor.index',$data);
    }

    public function datatables($id)
    {
        $user = auth()->user();
        $globals = Charge::where('plan_id', $user->bank_plan_id)->get();
        $datas = $globals;
        return Datatables::of($datas)
                        ->editColumn('name', function(Charge $data) {
                            return $data->name;
                        })
                        ->editColumn('percent', function(Charge $data)  {
                            if ($data->data){
                                return $data->data->percent_charge;
                            }
                            else {
                                return 0;
                            }
                        })
                        ->editColumn('fixed', function(Charge $data) {
                            if ($data->data){
                                return $data->data->fixed_charge;
                            }
                            else {
                                return 0;
                            }
                        })
                        ->editColumn('percent_customer', function(Charge $data) use($id) {
                            $customplan =  Charge::where('user_id',$id)->where('name', $data->name)->first();
                            if ($customplan){
                                return $customplan->data->percent_charge;
                            }
                            else {
                                return 0;
                            }
                        })
                        ->editColumn('fixed_customer', function(Charge $data) use($id) {
                            $customplan =  Charge::where('user_id',$id)->where('name', $data->name)->first();

                            if ($customplan){
                                return $customplan->data->fixed_charge;
                            }
                            else {
                                return 0;
                            }
                        })
                        ->addColumn('action', function (Charge $data) use($id) {
                            $customplan =  Charge::where('user_id',$id)->where('name', $data->name)->first();

                            if($customplan) {
                                return '<button type="button" class="btn btn-primary btn-big btn-rounded " onclick="getDetails('.$customplan->id.')" aria-haspopup="true" aria-expanded="false">
                                Edit
                                </button>';
                            }
                            else {

                                    return '<button type="button" class="btn btn-primary btn-big btn-rounded " data-id="'.$data->id.'" onclick="createDetails(\''.$data->id.'\')" aria-haspopup="true" aria-expanded="false">
                                    Edit
                                    </button>';
                            }
                        })

                        ->rawColumns(['action'])
                        ->toJson();
    }
    public function edit($id) {
        $plandetail = Charge::findOrFail($id);
        return view('user.supervisor.edit',compact('plandetail'));
    }

    public function create($id, $charge_id) {
        $global = Charge::findOrFail($charge_id);
        $plandetail = new Charge();
        $plandetail->name = $global->name;
        $plandetail->user_id = $id;
        $plandetail->plan_id = 0;
        $plandetail->data = $global->data;
        $plandetail->slug = $global->slug;
        return view('user.supervisor.edit',compact('plandetail'));
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

    public function createCharge(Request $request)
    {
        $data = new Charge();
        $data->name = $request->name;
        $data->user_id = $request->user_id;
        $data->plan_id = $request->plan_id;
        $data->slug = $request->slug;
        $inputs = $request->except(array('_token','name','user_id', 'plan_id', 'slug' ));
        foreach($inputs as $key =>  $input){
            $rules[$key] = 'required|numeric|min:0';
        }
        $request->validate($rules);
        $data->data = $inputs;

        $data->save();
        return redirect()->back()->with(array('message' => 'Customer Plan Create Successfully'));
    }
}
