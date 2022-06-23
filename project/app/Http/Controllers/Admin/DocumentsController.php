<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Generalsetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Datatables;

class DocumentsController extends Controller
{

    public function datatables()
    {
        $user = auth()->guard('admin')->user();
        $datas = Document::where('ins_id', $user->id)->orderBy('name','asc')->get();  
        //$datas = Document::orderBy('name','asc')->get();  
        
        return Datatables::of($datas)
                        ->addColumn('name',function(Document $data){
                            return $data->name;
                        })
                        ->addColumn('download',function(Document $data){
                            return '<a href="'.route('admin.documents.download',$data->id).'">
                            <button type="button" class="btn btn-primary btn-sm btn-rounded">'.__("Download").' </button></a>';
                            
                        })
                        ->addColumn('action', function(Document $data) {
                            return '<div class="btn-group mb-1">
                                        <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        '.'Actions' .'
                                        </button>
                                        <div class="dropdown-menu" x-placement="bottom-start">
                                        <a href="javascript:;" data-toggle="modal" data-target="#deleteModal" class="dropdown-item" data-href="'.  route('admin.documents.document-delete',$data->id).'">'.__("Delete").'</a>
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

    public function getDownload($id){
        //PDF file is stored under project/public/download/info.pdf
        $document = Document::find($id);
        $file= public_path("assets/documents/".$document->file);
        return Response::download($file);
    }

    public function destroy($id)
    {   
        $document = Document::findOrFail($id);
        if(file_exists(public_path("assets/documents/".$document->file)))
        {
            @unlink(public_path("assets/documents/".$document->file));
            $document->delete();
        }
        
        //--- Redirect Section
        $msg = 'Document Has Been Deleted Successfully.';
        return response()->json($msg);
        
    }
}
