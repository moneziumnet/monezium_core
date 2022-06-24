<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use App\Models\Admin;
use App\Models\Document;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class DocumentsController extends Controller
{

    public function datatables()
    {
        $user = auth()->guard('admin')->user();

        $datas = tenancy()->central(function ($tenant) use ($user) {
            $admin = Admin::where('email', $user->email)->first();
            return Document::where('ins_id', $admin->id)->orderBy('name','asc')->get();  
        });

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
        if($request->isMethod('post'))
        {
            $rules = [
                'document_name'   => 'required',
                'document_file'   => 'required'
            ];
    
            $validator = Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
    
            if(!$request->hasFile('document_file')) {
                return response()->json(array('errors' => 'Select your file'));
            }else{
                
                $allowedfileExtension = ['jpg','png','gif','pdf','jpeg','doc','docx','xls','xlsx'];
                $files = $request->file('document_file');
                
                $extension = $files->getClientOriginalExtension();
     
                $check = in_array($extension,$allowedfileExtension);
                
                if($check) {
                    $path = public_path() . '/assets/documents';
                    $files->move($path, $files->getClientOriginalName());
                    // $path = $request->image->store('public/uploads/app_sliders');
                    $file = $request->document_file->getClientOriginalName();
                            //  exit;
                    $user = auth()->guard('admin')->user();
                            //store image file into directory and db
                    $save = new Document();
                    // $save->title = $name;
                   $save->ins_id = $user->id;
                   $save->name = $request->input('document_name');
                   $save->file = $file;
                   $save->save();
                   return response()->json('Document Saved Successfully.');
                } else {
                    return response()->json('Please check your file extention and document name.');
                }
            }
        }else{
            return response()->json('Please check your file extention and document name.');
        }
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
           
        }
        $document->delete();
        //--- Redirect Section
        $msg = 'Document Has Been Deleted Successfully.';
        return response()->json($msg);
        
    }
}
