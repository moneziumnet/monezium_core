<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use App\Models\Admin;
use App\Models\Branch;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Validator;


class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    //*** JSON Request
    public function datatables()
    {
        $datas = Branch::with('institution')
                    ->whereHas('institution', function ($query){
                        $query->where('id','!=',1);
                    })
                    ->orderBy('id','desc')
                    ->get();
       // Admin::where('id','!=',1)->where('id','!=',Auth::guard('admin')->user()->id)->orderBy('id');

         //--- Integrating This Collection Into Datatables
         return Datatables::of($datas)
                            ->addColumn('ins_name', function(Branch $data) {
                                $ins_name = $data->institution ? $data->institution->name : '';
                                return $ins_name;
                            })
                            ->addColumn('action', function(Branch $data) {

                              return '<div class="btn-group mb-1">
                              <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                '.'Actions' .'
                              </button>
                              <div class="dropdown-menu" x-placement="bottom-start">
                                <a href="' . route('admin.branch.edit',$data->id) . '"  class="dropdown-item">'.__("Edit").'</a>
                                <a href="javascript:;" data-toggle="modal" data-target="#deleteModal" class="dropdown-item" data-href="'.  route('admin.branch.delete',$data->id).'">'.__("Delete").'</a>
                              </div>
                            </div>';

                            })
                            ->rawColumns(['action','ins_name'])
                            ->toJson(); //--- Returning Json Data To Client Side
    }

    //*** GET Request
  	public function index()
    {
        return view('admin.branch.index');
    }

    //*** GET Request
    public function create()
    {
        return view('admin.branch.create');
    }

    //*** POST Request
    public function store(Request $request)
    {
        $rules = [
            'branch_name'   => 'required',
            'ins_id'        => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
        return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
       //--- Validation Section Ends

        //--- Logic Section
        $data = new Branch();
        $input = $request->all();
       
        $input['name'] = $request->branch_name;
        $input['ins_id'] = $request->ins_id;
        
        $data->fill($input)->save();
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = __('New Data Added Successfully.').'<a href="'.route('admin.branch.index').'">'.__('View Lists.').'</a>';;

        return response()->json($msg);
        //--- Redirect Section Ends
 }


    public function edit($id)
    {
        $data = Branch::findOrFail($id);
        return view('admin.branch.edit',compact('data'));
    }

    public function update(Request $request,$id)
    {

        // if($id != Auth::guard('admin')->user()->id)
        // {
            $rules = [
                'branch_name'   => 'required',
                'ins_id'        => 'required'
            ];
    
            $validator = Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
            //--- Validation Section Ends

            //$input = $request->all();
            $data = Branch::findOrFail($id);
           
            $input['name']      = $request->branch_name;
            $input['ins_id']    = $request->ins_id;

            $data->update($input);
            $msg = 'Data Updated Successfully.'.'<a href="'.route("admin.branch.index").'">View List</a>';

            return response()->json($msg);
        // }
        // else{
        //     $msg = 'You can not change your role.';
        //     return response()->json($msg);
        // }

    }



    //*** GET Request Delete
    public function destroy($id)
    {
    	
        $data = Branch::findOrFail($id);
      
        $data->delete();
        //--- Redirect Section
        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }
}
