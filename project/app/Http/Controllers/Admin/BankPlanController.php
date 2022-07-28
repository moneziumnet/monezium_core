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
            'daily_send' => 'required|numeric|gt:0',
            'monthly_send' => 'required|numeric|gt:0',
            'daily_receive' => 'required|numeric|gt:01',
            'monthly_receive' => 'required|numeric|gt:0',
            'daily_withdraw' => 'required|numeric|gt:0',
            'monthly_withdraw' => 'required|numeric|gt:0',
            'loan_amount' => 'required|numeric|gt:0',
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
            'name' => 'Transfer Money',
            'slug' => 'transfer-money-'.$data->id,
            'data' => '{"percent_charge":"2","fixed_charge":"2","minimum":"10","maximum":"1000","daily_limit":"2000","monthly_limit":"5000"}',
            'plan_id' => $data->id,
        ]);
        DB::table('charges')->insert([
            'name' => 'Exchange Money',
            'slug' => 'exchange-money-'.$data->id,
            'data' => '{"percent_charge":"2","fixed_charge":"2","minimum":"10","maximum":"1000"}',
            'plan_id' => $data->id,
        ]);
        DB::table('charges')->insert([
            'name' => 'Request Money',
            'slug' => 'request-money-'.$data->id,
            'data' => '{"percent_charge":"1","fixed_charge":"2","minimum":"10","maximum":"2000"}',
            'plan_id' => $data->id,
        ]);

        DB::table('charges')->insert([
            'name' => 'Merchant Payment',
            'slug' => 'merchant-payment-'.$data->id,
            'data' => '{"percent_charge":"5","fixed_charge":"2"}',
            'plan_id' => $data->id,
        ]);

        DB::table('charges')->insert([
            'name' => 'Create Voucher',
            'slug' => 'create-voucher-'.$data->id,
            'data' => '{"percent_charge":"2","fixed_charge":"2","minimum":"10","maximum":"2000","commission":"10"}',
            'plan_id' => $data->id,
        ]);
        DB::table('charges')->insert([
            'name' => 'Create Invoice',
            'slug' => 'create-invoice-'.$data->id,
            'data' => '{"percent_charge":"5","fixed_charge":"2"}',
            'plan_id' => $data->id,
        ]);
        DB::table('charges')->insert([
            'name' => 'Make Escrow',
            'slug' => 'make-escrow-'.$data->id,
            'data' => '{"percent_charge":"5","fixed_charge":"2"}',
            'plan_id' => $data->id,
        ]);
        DB::table('charges')->insert([
            'name' => 'API Merchant Payment',
            'slug' => 'api-payment-'.$data->id,
            'data' => '{"percent_charge":"5","fixed_charge":"2"}',
            'plan_id' => $data->id,
        ]);
        DB::table('charges')->insert([
            'name' => 'Account Maintenance',
            'slug' => 'account-maintenance-'.$data->id,
            'data' => '{"percent_charge":"0","fixed_charge":"20"}',
            'plan_id' => $data->id,
        ]);
        DB::table('charges')->insert([
            'name' => 'Card Maintenance',
            'slug' => 'card-maintenance-'.$data->id,
            'data' => '{"percent_charge":"0","fixed_charge":"10"}',
            'plan_id' => $data->id,
        ]);
        DB::table('charges')->insert([
            'name' => 'Transaction 1',
            'slug' => 'transaction-1-'.$data->id,
            'data' => '{"percent_charge":"1","fixed_charge":"2","from":"1","till":"5000"}',
            'plan_id' => $data->id,
        ]);
        DB::table('charges')->insert([
            'name' => 'Transaction 2',
            'slug' => 'transaction-2-'.$data->id,
            'data' => '{"percent_charge":"1","fixed_charge":"2","from":"5001","till":"20000"}',
            'plan_id' => $data->id,
        ]);
        DB::table('charges')->insert([
            'name' => 'Transaction 3',
            'slug' => 'transaction-3-'.$data->id,
            'data' => '{"percent_charge":"1","fixed_charge":"2","from":"20001","till":"50000"}',
            'plan_id' => $data->id,
        ]);
        DB::table('charges')->insert([
            'name' => 'Referral',
            'slug' => 'referral-'.$data->id,
            'data' => '{"percent_charge":"1","fixed_charge":"2", "referral":"10","invited":"5"}',
            'plan_id' => $data->id,
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
            'daily_send' => 'required|numeric|gt:0',
            'monthly_send' => 'required|numeric|gt:0',
            'daily_receive' => 'required|numeric|gt:01',
            'monthly_receive' => 'required|numeric|gt:0',
            'daily_withdraw' => 'required|numeric|gt:0',
            'monthly_withdraw' => 'required|numeric|gt:0',
            'loan_amount' => 'required|numeric|gt:0',
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

        $msg = 'Data Updated Successfully.'.'<a href="'.route("admin.bank.plan.index").'">View Plan Lists</a>';
        return response()->json($msg);
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
