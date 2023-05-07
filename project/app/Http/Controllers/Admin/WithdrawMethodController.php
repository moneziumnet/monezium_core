<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Generalsetting;
use App\Models\Transaction;
use App\Models\Withdraw;
use App\Models\WithdrawMethod;
use App\Models\WithdrawLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Datatables;

class WithdrawMethodController extends Controller
{

    public function datatables()
    {
         $datas = WithdrawMethod::orderBy('id','desc')->get();


            return Datatables::of($datas)

            ->editColumn('id', function(WithdrawMethod $data) {
                return '<div>
                        '.$data->id.'

                </div>';
            })
            ->editColumn('method', function(WithdrawMethod $data){
                return  '<div>
                            '.$data->method.'

                        </div>';
            })
            ->editColumn('fixed', function(WithdrawMethod $data){
                $curr = Currency::where('is_default','=',1)->first();
                return  '<div>
                            '.$curr->symbol.$data->fixed.'

                        </div>';
            })
            ->editColumn('percentage', function(WithdrawMethod $data){
                $curr = Currency::where('is_default','=',1)->first();
              return '<div>
                            '.$curr->symbol.$data->percentage.'
                      </div>';
            })
            ->editColumn('status', function(WithdrawMethod $data) {
                $status = $data->status == 0 ? '<span class="badge badge-warning">Inactive</span>' : '<span class="badge badge-success">Active</span>';
                            return $status;
            })
            ->editColumn('created_at', function(WithdrawMethod $data) {
                return '<div>
                        '.$data->created_at.'
                </div>';
            })
            ->addColumn('action', function(WithdrawMethod $data) {

              return '<div class="btn-group mb-1">
              <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                '.'Actions' .'
              </button>
              <div class="dropdown-menu" x-placement="bottom-start">
                <a href="' . route('admin.withdraw.edit',$data->id) . '"  class="dropdown-item">'.__("Edit").'</a>
              </div>
            </div>';

            })

            ->rawColumns(['id','method','fixed','percentage','status','created_at','action'])
            ->toJson();

        }

    public function index(Request $request)
    {
        return view('admin.withdraw.index');
    }

    public function create()
    {
        $currencies = Currency::get();
        return view('admin.withdraw.create', compact('currencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:withdraw_methods,method',
            'min_amount' => 'required|numeric|gt:0',
            'max_amount' => 'required|numeric|gt:min_amount',
            'fixed_charge' => 'required|numeric|min:0',
            'percent_charge' => 'required|numeric|min:0',
            'status' => 'required|in:0,1',
            'currency' => 'required|integer',
            'withdraw_instruction' => 'required'
        ]);

        WithdrawMethod::create([
            'method' => $request->name,
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'fixed' => $request->fixed_charge,
            'percentage' => $request->percent_charge,
            'status' => $request->status,
            'currency_id' => $request->currency,
            'withdraw_instruction' => trim($request->withdraw_instruction)
        ]);

        return back()->with('success','Withdraw Method Created');
    }

    public function edit($id)
    {
        $currencies = Currency::get();
        $method = WithdrawMethod::findOrFail($id);
        return view('admin.withdraw.edit', compact('currencies','method'));
    }

    public function update(Request $request, WithdrawMethod $method)
    {
        $request->validate([
            'name' => 'required|unique:withdraw_methods,method,'.$method->id,
            'min_amount' => 'required|numeric|gt:0',
            'max_amount' => 'required|numeric|gt:min_amount',
            'fixed_charge' => 'required|numeric|min:0',
            'percent_charge' => 'required|numeric|min:0',
            'status' => 'required|in:0,1',
            'currency' => 'required|integer',
            'withdraw_instruction' => 'required'
        ]);


        $method->update([
            'method'                => $request->name,
            'min_amount'            => $request->min_amount,
            'max_amount'            => $request->max_amount,
            'fixed'                 => $request->fixed_charge,
            'percentage'               => $request->percent_charge,
            'status'                => $request->status,
            'currency_id'           => $request->currency,
            'withdraw_instruction'  => trim($request->withdraw_instruction)
        ]);
        return back()->with('success','Withdraw Method Updated');
    }

}
