<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Generalsetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Datatables;

class DocumentsController extends Controller
{

    public function datatables()
    {
        $user = Auth()->user();
        $datas = Document::where('ins_id', $user->id)->orderBy('name','asc')->get();  
        
        return Datatables::of($datas)
                        ->addColumn('name',function(Document $data){
                            return $data->name;
                        })
                        ->addColumn('download',function(Document $data){
                            return $data->file;
                        })
                        ->addColumn('action', function(Contact $data) {
                            return '<div class="btn-group mb-1">
                                        <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        '.'Actions' .'
                                        </button>
                                        <div class="dropdown-menu" x-placement="bottom-start">
                                        <a href="javascript:;" data-toggle="modal" data-target="#deleteModal" class="dropdown-item" data-href="'.  route('admin.contact.contact-delete',$data->id).'">'.__("Delete").'</a>
                                        </div>
                                    </div>';
                        })
                        
                        ->rawColumns(['name','download','action'])
                        ->toJson();
    }

    public function index(){
        //return view('admin.transaction.index');
    }

    public function create(Request $request)
    {   
        // $data = Auth::guard('admin')->user();

        // $contact = Contact::where('user_id', $data->id)->first();
        // $modules = Generalsetting::first();
        // // dd($modules);
        // return view('admin.create-contact', compact('data', 'modules', 'contact'));
    }

    public function edit($id)
    {
        // $data = Auth::guard('admin')->user();

        // $contact = Contact::where('id', $id)->first();
        // $modules = Generalsetting::first();
        
        // // dd($modules);
        // return view('admin.create-contact', compact('data', 'modules', 'contact', 'id'));
    }
}
