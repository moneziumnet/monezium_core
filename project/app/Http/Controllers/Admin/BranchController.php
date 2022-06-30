<?php

namespace App\Http\Controllers\Admin;

use Validator;
use Datatables;
use App\Models\Admin;
use App\Models\Branch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    //*** JSON Request
    public function datatables(Request $request)
    {
        $datas = Branch::where('subins_id', $request->id)->orderBy('id', 'asc')->get();
        return Datatables::of($datas)
            ->addColumn('action', function (Branch $data) {
                return '<div class="btn-group mb-1">
                              <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                ' . 'Actions' . '
                              </button>
                              <div class="dropdown-menu" x-placement="bottom-start">
                                <a href="' . route('admin.branch.edit', $data->id) . '"  class="dropdown-item">' . __("Edit") . '</a>
                                <a href="javascript:;" data-toggle="modal" data-target="#deleteModal" class="dropdown-item" data-href="' .  route('admin.branch.delete', $data->id) . '">' . __("Delete") . '</a>
                              </div>
                            </div>';
            })
            ->rawColumns(['action', 'ins_name'])
            ->toJson(); //--- Returning Json Data To Client Side
    }

    //*** GET Request
    public function create(Request $request)
    {
        $data = Admin::findOrFail($request->id);
        return view('admin.institution.subprofile.branch.create', compact('data'));
    }

    //*** POST Request
    public function store(Request $request)
    {
        $rules = [
            'branch_name'   => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $data = new Branch();
        $input = $request->all();

        $input['name'] = $request->branch_name;
        $input['subins_id'] = $request->subins_id;

        $data->fill($input)->save();

        $msg = __('New Data Added Successfully.') . '<a href="' . route('admin.subinstitution.branches',$data->subins_id) . '">' . __('View Lists.') . '</a>';;
        return response()->json($msg);
    }


    public function edit($id)
    {
        $data = Branch::findOrFail($id);
        return view('admin.institution.subprofile.branch.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'branch_name'   => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        $data = Branch::findOrFail($id);
        $input['name']      = $request->branch_name;
        $data->update($input);
        $msg = 'Data Updated Successfully.' . '<a href="' . route('admin.subinstitution.branches',$data->subins_id) . '">View List</a>';
        return response()->json($msg);
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
