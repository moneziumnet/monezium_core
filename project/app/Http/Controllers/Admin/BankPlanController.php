<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankPlan;
use App\Models\Charge;
use App\Models\Currency;
use App\Models\PlanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Datatables;

class BankPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function datatables()
    {
         $datas = BankPlan::orderBy('id','desc')->get();

         return Datatables::of($datas)
                            ->editColumn('created_at', function(BankPlan $data) {
                                return $data->created_at->toDateString();
                            })
                            ->editColumn('amount', function(BankPlan $data) {
                                $curr = Currency::where('is_default','=',1)->first();
                                return  '<div>
                                            '.$curr->symbol.$data->amount.'
                                        </div>';
                            })
                            ->addColumn('action', function(BankPlan $data) {
                                $delete = $data->id == 1 ? '':'<a href="javascript:;" data-toggle="modal" data-target="#deleteModal" class="dropdown-item" data-href="'.  route('admin.bank.plan.delete',$data->id).'">'.__("Delete").'</a>';
                                return '<div class="btn-group mb-1">
                                  <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    '.'Actions' .'
                                  </button>
                                  <div class="dropdown-menu" x-placement="bottom-start">
                                    <a href="' . route('admin.manage.charge',$data->id) . '"  class="dropdown-item">'.__("Charge").'</a>'.'
                                    <a href="' . route('admin.bank.plan.edit',$data->id) . '"  class="dropdown-item">'.__("Edit").'</a>'.$delete.'
                                  </div>
                                </div>';

                              })
                            ->rawColumns(['amount','action'])
                            ->toJson();
    }

    public function detaildatatables($id)
    {
        $datas = PlanDetail::where('plan_id', $id)->get();

        return Datatables::of($datas)
                           ->editColumn('name', function(PlanDetail $data) {
                               return $data->type;
                           })
                           ->editColumn('min', function(PlanDetail $data)  {
                                return $data->min;
                           })
                           ->editColumn('max', function(PlanDetail $data) {
                                return $data->max;
                           })
                           ->editColumn('daily_limit', function(PlanDetail $data)  {
                                return $data->daily_limit;
                           })
                           ->editColumn('monthly_limit', function(PlanDetail $data)  {
                                return $data->monthly_limit;
                           })
                           ->addColumn('action', function (PlanDetail $data)  {
                            return '<button type="button" class="btn btn-primary btn-big btn-rounded " data-id="'.$data->id.'" onclick="createglobalplan(\''.$data->id.'\')" aria-haspopup="true" aria-expanded="false">
                            Edit
                            </button>';
                           })

                           ->rawColumns(['action'])
                           ->toJson();
    }

    public function index(){
        return view('admin.bankplan.index');
    }

    public function create(){
        return view('admin.bankplan.create');
    }

    public function store(Request $request){
        $rules=[
            'title' => 'required',
            'amount' => 'required|numeric|min:0',
            // 'monthly_send' => 'required|numeric|gt:0',
            // 'daily_receive' => 'required|numeric|gt:01',
            // 'monthly_receive' => 'required|numeric|gt:0',
            // 'daily_withdraw' => 'required|numeric|gt:0',
            // 'monthly_withdraw' => 'required|numeric|gt:0',
            // 'loan_amount' => 'required|numeric|gt:0',
        ];

        $validator = Validator::make($request->all(),$rules);

        if($validator->fails()){
            return response()->json(['errors'=> $validator->getMessageBag()->toArray()]);
        }

        $data = new BankPlan();
        $data->title = $request->title;
        $data->amount = $request->amount;
        $data->daily_send = $request->daily_send;
        $data->monthly_send = $request->monthly_send;
        $data->daily_receive = $request->daily_receive;
        $data->monthly_receive = $request->monthly_receive;
        $data->daily_withdraw = $request->daily_withdraw;
        $data->monthly_withdraw = $request->monthly_withdraw;
        $data->loan_amount = $request->loan_amount;
        if($request->attribute){
            $data->attribute = json_encode($request->attribute,true);
        }
        $data->days = $request->days;
        $data->save();

        DB::table('charges')->insert([
            'name' => 'Deposit 1',
            'slug' => 'deposit',
            'data' => '{"percent_charge":"1","fixed_charge":"2","from":"1","till":"5000"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);
        DB::table('charges')->insert([
            'name' => 'Deposit 2',
            'slug' => 'deposit',
            'data' => '{"percent_charge":"1","fixed_charge":"3","from":"5001","till":"20000"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);
        DB::table('charges')->insert([
            'name' => 'Deposit 3',
            'slug' => 'deposit',
            'data' => '{"percent_charge":"1","fixed_charge":"2","from":"20001","till":"50000"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('charges')->insert([
            'name' => 'Send 1',
            'slug' => 'send',
            'data' => '{"percent_charge":"1","fixed_charge":"2","from":"1","till":"5000"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('charges')->insert([
            'name' => 'Send 2',
            'slug' => 'send',
            'data' => '{"percent_charge":"1","fixed_charge":"3","from":"5001","till":"20000"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('charges')->insert([
            'name' => 'Send 3',
            'slug' => 'send',
            'data' => '{"percent_charge":"1","fixed_charge":"2","from":"20001","till":"50000"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('charges')->insert([
            'name' => 'Recieve 1',
            'slug' => 'recieve',
            'data' => '{"percent_charge":"1","fixed_charge":"2","from":"1","till":"5000"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('charges')->insert([
            'name' => 'Recieve 2',
            'slug' => 'recieve',
            'data' => '{"percent_charge":"1","fixed_charge":"3","from":"5001","till":"20000"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('charges')->insert([
            'name' => 'Recieve 3',
            'slug' => 'recieve',
            'data' => '{"percent_charge":"1","fixed_charge":"2","from":"20001","till":"50000"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('charges')->insert([
            'name' => 'Escrow 1',
            'slug' => 'escrow',
            'data' => '{"percent_charge":"1","fixed_charge":"2","from":"1","till":"5000"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('charges')->insert([
            'name' => 'Escrow 2',
            'slug' => 'escrow',
            'data' => '{"percent_charge":"1","fixed_charge":"3","from":"5001","till":"20000"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('charges')->insert([
            'name' => 'Escrow 3',
            'slug' => 'escrow',
            'data' => '{"percent_charge":"1","fixed_charge":"2","from":"20001","till":"50000"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('charges')->insert([
            'name' => 'Withdraw 1',
            'slug' => 'withdraw',
            'data' => '{"percent_charge":"1","fixed_charge":"2","from":"1","till":"5000"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('charges')->insert([
            'name' => 'Withdraw 2',
            'slug' => 'withdraw',
            'data' => '{"percent_charge":"1","fixed_charge":"3","from":"5001","till":"20000"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('charges')->insert([
            'name' => 'Withdraw 3',
            'slug' => 'withdraw',
            'data' => '{"percent_charge":"1","fixed_charge":"2","from":"20001","till":"50000"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('charges')->insert([
            'name' => 'Account Maintenance',
            'slug' => 'account-maintenance',
            'data' => '{"percent_charge":"2","fixed_charge":"2"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('charges')->insert([
            'name' => 'Card Maintenance',
            'slug' => 'card-maintenance',
            'data' => '{"percent_charge":"2","fixed_charge":"2"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('charges')->insert([
            'name' => 'Account Opening',
            'slug' => 'account-open',
            'data' => '{"percent_charge":"2","fixed_charge":"2"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('charges')->insert([
            'name' => 'Card Issuance',
            'slug' => 'card-issuance',
            'data' => '{"percent_charge":"2","fixed_charge":"2"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('charges')->insert([
            'name' => 'Password reset by Staff',
            'slug' => 'manual',
            'data' => '{"percent_charge":"2","fixed_charge":"2"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('charges')->insert([
            'name' => 'Payment tracking',
            'slug' => 'manual',
            'data' => '{"percent_charge":"2","fixed_charge":"2"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('charges')->insert([
            'name' => 'Security key reset',
            'slug' => 'manual',
            'data' => '{"percent_charge":"2","fixed_charge":"2"}',
            'plan_id' => $data->id,
            'user_id' => 0
        ]);

        DB::table('plan_details')->insert([
            'type' => 'deposit',
            'plan_id' => $data->id,
            'min' => 100,
            'max' => 10000,
            'daily_limit' => 10000,
            'monthly_limit' => 250000,
        ]);

        DB::table('plan_details')->insert([
            'type' => 'send',
            'plan_id' => $data->id,
            'min' => 100,
            'max' => 10000,
            'daily_limit' => 10000,
            'monthly_limit' => 250000,
        ]);

        DB::table('plan_details')->insert([
            'type' => 'recieve',
            'plan_id' => $data->id,
            'min' => 100,
            'max' => 10000,
            'daily_limit' => 10000,
            'monthly_limit' => 250000,
        ]);

        DB::table('plan_details')->insert([
            'type' => 'escrow',
            'plan_id' => $data->id,
            'min' => 100,
            'max' => 10000,
            'daily_limit' => 10000,
            'monthly_limit' => 250000,
        ]);

        DB::table('plan_details')->insert([
            'type' => 'withdraw',
            'plan_id' => $data->id,
            'min' => 100,
            'max' => 10000,
            'daily_limit' => 10000,
            'monthly_limit' => 250000,
        ]);

        $msg = 'New Data Added Successfully.'.'<a href="'.route("admin.bank.plan.index").'">View Plan Lists</a>';
        return response()->json($msg);
    }

    public function edit($id){
        $data = BankPlan::findOrFail($id);
        $data['attributes'] = json_decode($data->attribute,true);
        $data['data'] = $data;
        $data['plan_details'] = PlanDetail::where('plan_id', $id)->get();
        return view('admin.bankplan.edit',$data);
    }

    public function update(Request $request,$id){
        $rules=[
            'title' => 'required',
            'amount' => 'required|numeric|min:0',
            // 'daily_send' => 'required|numeric|gt:0',
            // 'monthly_send' => 'required|numeric|gt:0',
            // 'daily_receive' => 'required|numeric|gt:01',
            // 'monthly_receive' => 'required|numeric|gt:0',
            // 'daily_withdraw' => 'required|numeric|gt:0',
            // 'monthly_withdraw' => 'required|numeric|gt:0',
            // 'loan_amount' => 'required|numeric|gt:0',
        ];

        $validator = Validator::make($request->all(),$rules);

        if($validator->fails()){
            return response()->json(['errors'=> $validator->getMessageBag()->toArray()]);
        }

        $data = BankPlan::findOrFail($id);
        $data->title = $request->title;
        $data->amount = $request->amount;
        $data->daily_send = $request->daily_send;
        $data->monthly_send = $request->monthly_send;
        $data->daily_receive = $request->daily_receive;
        $data->monthly_receive = $request->monthly_receive;
        $data->daily_withdraw = $request->daily_withdraw;
        $data->monthly_withdraw = $request->monthly_withdraw;
        $data->loan_amount = $request->loan_amount;
        $data->days = $request->days;
        if($request->attribute){
            $data->attribute = json_encode($request->attribute,true);
        }
        $data->update();

        $msg = 'Data Updated Successfully.'.'<a href="'.route("admin.bank.plan.index").'">View Plan Lists</a>';
        return response()->json($msg);
    }

    public function plandetailupdate(Request $request, $id) {
        $rules=[
            'detail_type' => 'required',
            'detail_min' => 'required|numeric|min:0',
            'detail_max' => 'required|numeric|gt:0',
            'detail_daily' => 'required|numeric|gt:0',
            'detail_monthly' => 'required|numeric|gt:0',
        ];

        $validator = Validator::make($request->all(),$rules);

        if($validator->fails()){
            return response()->json(['errors'=> $validator->getMessageBag()->toArray()]);
        }

        $data = PlanDetail::findOrFail($id);
        $data->min = $request->detail_min;
        $data->max = $request->detail_max;
        $data->daily_limit = $request->detail_daily;
        $data->monthly_limit = $request->detail_monthly;
        $data->update();


        return back()->with('message','Data Updated Successfully.');
    }

    public function plandetailget($id) {
        $detail = PlanDetail::findOrFail($id);
        return view('admin.bankplan.detail',compact('detail'));
    }

    public function destroy($id){
        if($id == 1){
            return response()->json('This plan should not be removed.');
        }
        BankPlan::findOrFail($id)->delete();

        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
    }
}
