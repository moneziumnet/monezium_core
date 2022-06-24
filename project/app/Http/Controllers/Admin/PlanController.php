<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Charge;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Datatables;

class PlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function datatables()
    {
        $datas = Plan::orderBy('id', 'desc')->get();

        return Datatables::of($datas)
            ->editColumn('created_at', function (Plan $data) {
                return $data->created_at->toDateString();
            })
            ->editColumn('price', function (Plan $data) {
                $curr = Currency::where('is_default', '=', 1)->first();
                return  '<div>'.$curr->symbol.$data->price.'</div>';
            })
            ->editColumn('duration', function (Plan $data) {
                return  '<div>
                '. $data->duration . ' ' . $data->durationtype . '
                </div>';
            })
            ->addColumn('action', function (Plan $data) {
                $delete = $data->id == 1 ? '' : '<a href="javascript:;" data-toggle="modal" data-target="#deleteModal" class="dropdown-item" data-href="' .  route('admin.plan.delete', $data->id) . '">' . __("Delete") . '</a>';
                return '<div class="btn-group mb-1">
                                  <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    ' . 'Actions' . '
                                  </button>
                                  <div class="dropdown-menu" x-placement="bottom-start">
                                    <a href="' . route('admin.plan.edit', $data->id) . '"  class="dropdown-item">' . __("Edit") . '</a>' . $delete . '
                                  </div>
                                </div>';
            })
            ->rawColumns(['price','duration', 'action'])
            ->toJson();
    }

    public function index()
    {
        return view('admin.subscriptionplan.index');
    }

    public function create()
    {
        return view('admin.subscriptionplan.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'price' => 'required|numeric|min:0',
            'duration' => 'required',
            'durationtype' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->getMessageBag()->toArray()]);
        }

        $data = new Plan();
        $data->name = $request->name;
        $data->price = $request->price;
        $data->duration = $request->duration;
        $data->durationtype = $request->durationtype;

        if ($request->attribute) {
            $data->attribute = json_encode($request->attribute, true);
        }
        $data->save();

        $msg = 'New Data Added Successfully.' . '<a href="' . route("admin.plan.index") . '">View Plan Lists</a>';
        return response()->json($msg);
    }

    public function edit($id)
    {
        $data = Plan::findOrFail($id);
        $data['attributes'] = json_decode($data->attribute, true);
        $data['data'] = $data;
        return view('admin.subscriptionplan.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required',
            'price' => 'required|numeric|min:0',
            'duration' => 'required',
            'durationtype' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->getMessageBag()->toArray()]);
        }

        $data = Plan::findOrFail($id);
        $data->name = $request->name;
        $data->price = $request->price;
        $data->duration = $request->duration;
        $data->durationtype = $request->durationtype;

        if ($request->attribute) {
            $data->attribute = json_encode($request->attribute, true);
        }
        $data->update();

        $msg = 'Data Updated Successfully.' . '<a href="' . route("admin.plan.index") . '">View Plan Lists</a>';
        return response()->json($msg);
    }

    public function destroy($id)
    {
        if ($id == 1) {
            return response()->json('This plan should not be removed.');
        }
        Plan::findOrFail($id)->delete();

        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
    }
}
