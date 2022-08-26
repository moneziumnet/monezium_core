<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use App\Models\CryptoCurrency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Validator;

class CryptoCurrencyController extends Controller
{
   public function __construct()
    {
        $this->middleware('auth:admin');
    }


    public function datatables()
    {
        $datas = CryptoCurrency::orderBy('id','desc');
         return Datatables::of($datas)
                            ->addColumn('action', function(CryptoCurrency $data) {
                                $delete = $data->is_default == 1 ? '':'<a href="javascript:;" data-href="' . route('admin.crypto.currency.delete',$data->id) . '" data-toggle="modal" data-target="#deleteModal" class="dropdown-item">'.__("Delete").'</a>';
                                return '<div class="btn-group mb-1">
                              <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                '.'Actions' .'
                              </button>
                              <div class="dropdown-menu" x-placement="bottom-start">
                                <a href="' . route('admin.crypto.currency.edit',$data->id) . '"  class="dropdown-item">'.__("Edit").'</a>'.$delete.'

                              </div>
                            </div>';
                            })
                            ->rawColumns(['action'])
                            ->toJson();
    }

    public function index()
    {
        return view('admin.cryptocurrency.index');
    }

    public function create()
    {
        return view('admin.cryptocurrency.create');
    }

    public function store(Request $request)
    {

        $data = new CryptoCurrency();
        $input = $request->all();

        $data->fill($input)->save();


        $msg = __('New Data Added Successfully.').' '.'<a href="'.route('admin.crypto.currency.index').'"> '.__('View Lists.').'</a>';
        return response()->json($msg);

    }

    public function edit($id)
    {
        $data = CryptoCurrency::findOrFail($id);
        return view('admin.cryptocurrency.edit',compact('data'));
    }

    public function update(Request $request, $id)
    {
        $data = CryptoCurrency::findOrFail($id);
        $input = $request->all();
        $data->update($input);
        $msg = __('Data Updated Successfully.').' '.'<a href="'.route('admin.crypto.currency.index').'"> '.__('View Lists.').'</a>';
        return response()->json($msg);
    }

    public function destroy($id)
    {
        if($id == 1)
        {
        return __("You cant't remove the main currency.");
        }
        $data = CryptoCurrency::findOrFail($id);
        $data->delete();
        $msg = __('Data Deleted Successfully.');
        return response()->json($msg);
    }

}
