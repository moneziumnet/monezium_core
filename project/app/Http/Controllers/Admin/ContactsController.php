<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Contact;
use App\Models\Generalsetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Datatables;

class ContactsController extends Controller
{

    public function datatables()
    {
        $user = auth()->guard('admin')->user();
        $datas = Contact::where('user_id', $user->id)->orderBy('id','asc')->get();  
        // $datas = Contact::orderBy('id','asc')->get();  
        
        return Datatables::of($datas)
                        ->addColumn('contact',function(Contact $data){
                            return $data->contact;
                        })
                        ->addColumn('fname',function(Contact $data){
                            return $data->full_name;
                        })
                        ->addColumn('email_add',function(Contact $data){
                            return $data->c_email;
                        })
                        ->addColumn('address',function(Contact $data){
                            return $data->c_address;
                        })
                        ->addColumn('phone',function(Contact $data){
                            return $data->c_phone;
                        })
                       
                        ->addColumn('action', function(Contact $data) {
                            return '<div class="btn-group mb-1">
                                        <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        '.'Actions' .'
                                        </button>
                                        <div class="dropdown-menu" x-placement="bottom-start">
                                        <a href="' . route('admin.contact.contact-edit',$data->id) . '" class="dropdown-item" >'.__("Edit").'</a>
                                        <a href="javascript:;" data-toggle="modal" data-target="#deleteModal" class="dropdown-item" data-href="'.  route('admin.contact.contact-delete',$data->id).'">'.__("Delete").'</a>
                                        </div>
                                    </div>';
                        })
                        
                        ->rawColumns(['contact','fname','email_add', 'address', 'phone','action'])
                        ->toJson();
    }

    public function index(){
        //return view('admin.transaction.index');
    }

    public function create(Request $request)
    {   
        $data = Auth::guard('admin')->user();

        $contact = Contact::where('user_id', $data->id)->first();
        $modules = Generalsetting::first();
        // dd($modules);
        return view('admin.create-contact', compact('data', 'modules', 'contact'));
    }

    public function edit($id)
    {
        $data = Auth::guard('admin')->user();

        $contact = Contact::where('id', $id)->first();
        $modules = Generalsetting::first();
        
        // dd($modules);
        return view('admin.create-contact', compact('data', 'modules', 'contact', 'id'));
    }

    public function destroy($id)
    {
        $data = Contact::findOrFail($id);
        $data->delete();
       
        //--- Redirect Section
        $msg = 'Contact Has Been Deleted Successfully.';
        return response()->json(['succes'=> $msg]);
    }
}
