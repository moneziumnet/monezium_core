<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use App\Models\Admin;
use App\Models\Currency;
use App\Models\SubInsBank;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SubInsBankController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function datatables(Request $request)
    {
         $ins_id = $request->id;
         $datas = SubInsBank::where('ins_id', $ins_id)->orderBy('id','desc')->get();

         return Datatables::of($datas)

                            ->editColumn('name', function(SubInsBank $data) {
                                return  '<div>
                                            <h6 class="text-primary">'.$data->name.'</h6>
                                            Processing Time : '.$data->address.'
                                        </div>';
                            }) 
                            ->editColumn('min_limit', function(SubInsBank $data){
                                $curr = Currency::where('is_default','=',1)->first();
                                return '<div>
                                            Min : <span class="text-primary">'.$curr->symbol.round($data->min_limit,2).'</span>
                                            <br>
                                            Max : <span class="text-primary">'.$curr->symbol.round($data->max_limit,2).'</span>
                                        </div>';
                            })
                            ->editColumn('fixed_charge', function(SubInsBank $data){
                                $curr = Currency::where('is_default','=',1)->first();
                                return '<div>
                                            Fixed : <span class="text-primary">'.$curr->symbol.round($data->fixed_charge,2).'</span>
                                            <br>
                                            Percent : <span class="text-primary">'.round($data->percent_charge,2).'%</span>
                                        </div>';
                            })
                            ->editColumn('status', function(SubInsBank $data) {
                                $status      = $data->status == 1 ? _('activated') : _('deactivated');
                                $status_sign = $data->status == 1 ? 'success'   : 'danger';

                                return '<div class="btn-group mb-1">
                                <button type="button" class="btn btn-'.$status_sign.' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                  '.$status .'
                                </button>
                                <div class="dropdown-menu" x-placement="bottom-start">
                                  <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.subinstitution.banks.status',['id1' => $data->id, 'status' => 1]).'">'.__("activated").'</a>
                                  <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.subinstitution.banks.status',['id1' => $data->id, 'status' => 0]).'">'.__("deactivated").'</a>
                                </div>
                              </div>';
                            })
                            ->addColumn('action', function(SubInsBank $data) {

                                return '<div class="btn-group mb-1">
                                  <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    '.'Actions' .'
                                  </button>
                                  <div class="dropdown-menu" x-placement="bottom-start">
                                    <a href="' . route('admin.subinstitution.banks.edit',$data->id) . '"  class="dropdown-item">'.__("Edit").'</a>
                                    <a href="javascript:;" data-toggle="modal" data-target="#deleteModal" class="dropdown-item" data-href="'.  route('admin.subinstitution.banks.delete',$data->id).'">'.__("Delete").'</a>
                                  </div>
                                </div>';
  
                              })
                            ->rawColumns(['name','min_limit','fixed_charge','status','action'])
                            ->toJson();
    }

    public function index(){
        return view('admin.institution.subprofile.bank.index');
    }

    public function create(Request $request){
        $data['currency'] = Currency::whereIsDefault(1)->first();
        $data['data'] = Admin::findOrFail($request->id);
        return view('admin.institution.subprofile.bank.create',$data);
    }

    public function store(Request $request){
        $rules = [
            'name' => 'required|max:255',
            'address' => 'required',
            'min_limit' => 'required',
            'max_limit' => 'required',
            'daily_maximum_limit' => 'required',
            'daily_total_transaction' => 'required',
            'monthly_maximum_limit' => 'required',
            'monthly_total_transaction' => 'required',
            'fixed_charge' => 'required',
            'percent_charge' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $input = $request->all();
        $data = new SubInsBank();

        if($request->form_builder){
            $input['required_information'] = json_encode(array_values($request->form_builder));
        }
        $data->fill($input)->save();

        $msg = 'New Bank Added Successfully.<a href="'.route('admin.subinstitution.banks',$data->ins_id).'">View Bank Lists.</a>';
        return response()->json($msg); 
    }

    public function edit(Request $request, $id){
        $data['data'] = SubInsBank::findOrFail($id);
        $data['currency'] = Currency::whereIsDefault(1)->first();
        $data['informations'] = json_decode($data['data']->required_information,true);

        return view('admin.institution.subprofile.bank.edit',$data);
    }

    public function update(Request $request, $id){
        $rules = [
            'name' => 'required|max:255',
            'address' => 'required',
            'min_limit' => 'required',
            'max_limit' => 'required',
            'daily_maximum_limit' => 'required',
            'daily_total_transaction' => 'required',
            'monthly_maximum_limit' => 'required',
            'monthly_total_transaction' => 'required',
            'fixed_charge' => 'required',
            'percent_charge' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $data = SubInsBank::findOrFail($id);
        $input = $request->all();

        if($request->form_builder){
            $input['required_information'] = json_encode(array_values($request->form_builder));
        }
        $data->update($input);

        $msg = 'Bank Updated Successfully.<a href="'.route('admin.subinstitution.banks',$data->ins_id).'">View Bank Lists.</a>';
        return response()->json($msg); 
    }

    public function status($id1,$id2)
    {
        $data = SubInsBank::findOrFail($id1);
        $data->status = $id2;
        $data->update();

        $msg = __('Status Updated Successfully.');
        return response()->json($msg);
    }

    public function destroy($id)
    {
        $data = SubInsBank::findOrFail($id);
        $data->delete();

        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);       
    }

}
