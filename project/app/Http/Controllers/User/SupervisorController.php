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

                                    return '<button type="button" class="btn btn-primary btn-big btn-rounded " data-id="'.$data->name.'" onclick="createDetails(\''.$data->name.'\')" aria-haspopup="true" aria-expanded="false">
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

    public function create($id, $name) {
        $plandetail = new Charge();
        $plandetail->name = $name;
        $plandetail->user_id = $id;
        $plandetail->plan_id = 0;
        switch ($name) {
            case 'Transfer Money':
                $plandetail->data = json_decode('{"percent_charge":"2","fixed_charge":"2"}');
                $plandetail->slug = 'transfer-money-0-'.$id;
                break;
            case 'Exchange Money':
                $plandetail->data = json_decode('{"percent_charge":"2","fixed_charge":"2"}');
                $plandetail->slug = 'exchange-money-0-'.$id;
                break;
            case 'Request Money':
                $plandetail->data = json_decode('{"percent_charge":"1","fixed_charge":"2"}');
                $plandetail->slug = 'request-money-0-'.$id;
                break;
            case 'Merchant Payment':
                $plandetail->data = json_decode('{"percent_charge":"5","fixed_charge":"2"}');
                $plandetail->slug = 'merchant-payment-0-'.$id;
                break;
            case 'Create Voucher':
                $plandetail->data = json_decode('{"percent_charge":"2","fixed_charge":"2"}');
                $plandetail->slug = 'create-voucher-0-'.$id;
                break;
            case 'Create Invoice':
                $plandetail->data = json_decode('{"percent_charge":"5","fixed_charge":"2"}');
                $plandetail->slug = 'create-invoice-0-'.$id;
                break;
            case 'Make Escrow':
                $plandetail->data = json_decode('{"percent_charge":"5","fixed_charge":"2"}');
                $plandetail->slug = 'make-escrow-0-'.$id;
                break;
            case 'API Merchant Payment':
                $plandetail->data = json_decode('{"percent_charge":"5","fixed_charge":"2"}');
                $plandetail->slug = 'api-payment-0-'.$id;
                break;
            case 'Account Maintenance':
                $plandetail->data = json_decode('{"percent_charge":"0","fixed_charge":"20"}');
                $plandetail->slug = 'account-maintenance-0-'.$id;
                break;
            case 'Card Maintenance':
                $plandetail->data = json_decode('{"percent_charge":"0","fixed_charge":"20"}');
                $plandetail->slug = 'card-maintenance-0-'.$id;
                break;
            case 'Transaction 1':
                $plandetail->data = json_decode('{"percent_charge":"1","fixed_charge":"2","from":"1","till":"5000"}');
                $plandetail->slug = 'transaction-1-0-'.$id;
                break;
            case 'Transaction 2':
                $plandetail->data = json_decode('{"percent_charge":"1","fixed_charge":"2","from":"5001","till":"20000"}');
                $plandetail->slug = 'transaction-2-0-'.$id;
                break;
            case 'Transaction 3':
                $plandetail->data = json_decode('{"percent_charge":"1","fixed_charge":"2","from":"20001","till":"50000"}');
                $plandetail->slug = 'transaction-3-0-'.$id;
                break;
            case 'Referral':
                $plandetail->data = json_decode('{"percent_charge":"1","fixed_charge":"2", "referral":"10","invited":"5"}');
                $plandetail->slug = 'referral-0-'.$id;
                break;
        }
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
