<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use App\Models\Admin;
use App\Models\Contact;
use App\Models\Document;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Validator;


class InstitutionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    //*** JSON Request
    public function datatables()
    {
        $datas = Admin::where('id', '!=', 1)->where('id', '!=', Auth::guard('admin')->user()->id)->orderBy('id');

        //--- Integrating This Collection Into Datatables
        return Datatables::of($datas)
            ->addColumn('status', function (Admin $data) {
                $status      = $data->status == 0 ? __('Block') : __('Unblock');
                $status_sign = $data->status == 0 ? 'danger'   : 'success';

                return '<div class="btn-group mb-1">
                                    <button type="button" class="btn btn-' . $status_sign . ' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        ' . $status . '
                                    </button>
                                    <div class="dropdown-menu" x-placement="bottom-start">
                                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="' . route('admin-staff-block', ['id1' => $data->id, 'id2' => 1]) . '">' . __("Unblock") . '</a>
                                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="' . route('admin-staff-block', ['id1' => $data->id, 'id2' => 0]) . '">' . __("Block") . '</a>
                                    </div>
                                    </div>';
            })

            ->addColumn('action', function (Admin $data) {

                return '<div class="btn-group mb-1">
                              <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                ' . 'Actions' . '
                              </button>
                              <div class="dropdown-menu" x-placement="bottom-start">
                                <a href="' . route('admin.institution.profile', $data->id) . '"  class="dropdown-item">' . __("Profile") . '</a>
                                <a href="javascript:;" data-toggle="modal" data-target="#deleteModal" class="dropdown-item" data-href="' .  route('admin.institution.delete', $data->id) . '">' . __("Delete") . '</a>
                              </div>
                            </div>';
            })
            ->rawColumns(['action', 'status'])
            ->toJson(); //--- Returning Json Data To Client Side
    }

    //*** GET Request
    public function index()
    {
        return view('admin.institution.index');
    }

    //*** GET Request
    public function create()
    {
        return view('admin.institution.create');
    }

    //*** POST Request
    public function store(Request $request)
    {
        $rules = [
            'email' => 'required|unique:admins',
            'photo' => 'required|mimes:jpeg,jpg,png,svg',
            'password' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = new Admin();
        $input = $request->all();
        if ($file = $request->file('photo')) {
            $name = Str::random(8) . time() . '.' . $file->getClientOriginalExtension();
            $file->move('assets/images', $name);
            $input['photo'] = $name;
        }

        $input['password'] = bcrypt($request['password']);
        $data->fill($input)->save();
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = __('New Data Added Successfully.') . '<a href="' . route('admin.institution.index') . '">' . __('View Lists.') . '</a>';;

        return response()->json($msg);
        //--- Redirect Section Ends
    }

    public function block($id1, $id2)
    {
        $user = Admin::findOrFail($id1);
        $user->status = $id2;
        $user->update();
        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
    }

    public function edit($id)
    {
        $data = Admin::findOrFail($id);
        return view('admin.institution.edit', compact('data'));
    }

    public function profile($id)
    {
        $data = Admin::findOrFail($id);
        //$contact = Contact::where('user_id', $data->id)->first();
        return view('admin.institution.profile', compact('data'));
    }

    public function moduleupdate(Request $request, $id)
    {
        if ($id != Auth::guard('admin')->user()->id) {
            $input = $request->all();
            $data = Admin::findOrFail($id);
            if (!empty($request->section)) {
                $input['section'] = implode(" , ", $request->section);
            } else {
                $input['section'] = '';
            }
            $data->section = $input['section'];
            $data->update();
            $msg = 'Data Updated Successfully.';

            return response()->json($msg);
        } else {
            $msg = 'You can not change your role.';
            return response()->json($msg);
        }
    }

    public function update(Request $request, $id)
    {

        if ($id != Auth::guard('admin')->user()->id) {
            $rules =
                [
                    'photo' => 'mimes:jpeg,jpg,png,svg',
                    'email' => 'unique:admins,email,' . $id
                ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
            //--- Validation Section Ends
            $input = $request->all();
            $data = Admin::findOrFail($id);
            if ($file = $request->file('photo')) {
                $name = Str::random(8) . time() . '.' . $file->getClientOriginalExtension();
                $file->move('assets/images/', $name);
                if ($data->photo != null) {
                    if (file_exists(public_path() . '/assets/images/' . $data->photo)) {
                        unlink(public_path() . '/assets/images/' . $data->photo);
                    }
                }
                $input['photo'] = $name;
            }
            if ($request->password == '') {
                $input['password'] = $data->password;
            } else {
                $input['password'] = Hash::make($request->password);
            }

            if (!empty($request->section)) {
                $input['section'] = implode(" , ", $request->section);
            } else {
                $input['section'] = '';
            }

            $data->update($input);
            $msg = 'Data Updated Successfully.';

            return response()->json($msg);
        } else {
            $msg = 'You can not change your role.';
            return response()->json($msg);
        }
    }

    public function contactsDatatables($id)
    {
        $datas = Contact::where('user_id', $id)->orderBy('id', 'asc')->get();
        return Datatables::of($datas)
            ->addColumn('contact', function (Contact $data) {
                return $data->contact;
            })
            ->addColumn('fname', function (Contact $data) {
                return $data->full_name;
            })
            ->addColumn('email_add', function (Contact $data) {
                return $data->c_email;
            })
            ->addColumn('address', function (Contact $data) {
                return $data->c_address;
            })
            ->addColumn('phone', function (Contact $data) {
                return $data->c_phone;
            })

            ->addColumn('action', function (Contact $data) {
                return '<div class="btn-group mb-1">
                                        <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        ' . 'Actions' . '
                                        </button>
                                        <div class="dropdown-menu" x-placement="bottom-start">
                                        <a href="' . route('admin.contact.contact-edit', $data->id) . '" class="dropdown-item" >' . __("Edit") . '</a>
                                        <a href="javascript:;" data-toggle="modal" data-target="#deleteModal" class="dropdown-item" data-href="' .  route('admin.contact.contact-delete', $data->id) . '">' . __("Delete") . '</a>
                                        </div>
                                    </div>';
            })

            ->rawColumns(['contact', 'fname', 'email_add', 'address', 'phone', 'action'])
            ->toJson();
    }

    public function documentsDatatables($id)
    {
        $datas = Document::where('ins_id', $id)->orderBy('name', 'asc')->get();
        //$datas = Document::orderBy('name','asc')->get();  

        return Datatables::of($datas)
            ->addColumn('name', function (Document $data) {
                return $data->name;
            })
            ->addColumn('download', function (Document $data) {
                return '<a href="' . route('admin.documents.download', $data->id) . '">
                            <button type="button" class="btn btn-primary btn-sm btn-rounded">' . __("Download") . ' </button></a>';
            })
            ->addColumn('action', function (Document $data) {
                return '<div class="btn-group mb-1">
                                        <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        ' . 'Actions' . '
                                        </button>
                                        <div class="dropdown-menu" x-placement="bottom-start">
                                        <a href="javascript:;" data-toggle="modal" data-target="#deleteModal" class="dropdown-item" data-href="' .  route('admin.documents.document-delete', $data->id) . '">' . __("Delete") . '</a>
                                        </div>
                                    </div>';
            })

            ->rawColumns(['name', 'download', 'action'])
            ->toJson();
    }

    public function createContact(Request $request, $id)
    {
        //--- Validation Section

        $rules = [
            'fullname'      => 'required',
            'contact'       => 'required',
            'your_email'    => 'required',
            'your_phone'    => 'required',
            'your_address'  => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends
        $input = $request->all();
        //dd($input);
        if ($request->input('contact_id') > 0) {
            $contact =   Contact::findOrFail($request->input('contact_id'));

            $contact->full_name    =  $request->input('fullname');
            $contact->contact     =  $request->input('contact');

            // $contact->dob           = $request->input('dob') ? date('Y-m-d', strtotime($request->input('dob'))) : '';
            $contact->personal_code = $request->input('personal_code');
            $contact->c_email       = $request->input('your_email');
            $contact->c_phone       = $request->input('your_phone');
            $contact->c_address     = $request->input('your_address');
            $contact->c_city        = $request->input('c_city');
            $contact->c_zip_code    = $request->input('c_zipcode');
            $contact->c_country     = $request->input('c_country_id');
            $contact->id_number     = $request->input('your_id');
            $contact->issued_authority = $request->input('issued_authority');
        } else {
            $contact                = new Contact();
            $contact->full_name     =  $request->input('fullname');
            $contact->contact       =  $request->input('contact');
            $contact->user_id       =  $id;
            $contact->dob           = $request->input('dob')  != null ? date('Y-m-d', strtotime($request->input('dob'))) : null;
            $contact->personal_code = $request->input('personal_code');
            $contact->c_email       = $request->input('your_email');
            $contact->c_phone       = $request->input('your_phone');
            $contact->c_address     = $request->input('your_address');
            $contact->c_city        = $request->input('c_city');
            $contact->c_zip_code    = $request->input('c_zipcode');
            $contact->c_country     = $request->input('c_country_id');
            $contact->id_number             = $request->input('your_id');
            $contact->issued_authority      = $request->input('issued_authority');
            $contact->date_of_issue         = $request->input('date_of_issue') != null  ? date('Y-m-d', strtotime($request->input('date_of_issue'))) : null;
            $contact->date_of_expire        = $request->input('date_of_expire') != null ? date('Y-m-d', strtotime($request->input('date_of_expire'))) : null;
            //dd($contact);
            if ($contact->save()) {
                $msg = 'Successfully save contact information.';
                return response()->json($msg);
            } else {
                $msg = 'Something went wrong. Please try again.';
                return response()->json($msg);
            }
        }
    }

    public function createDocument(Request $request, $id)
    {
        if ($request->isMethod('post')) {
            $rules = [
                'document_name'   => 'required',
                'document_file'   => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }

            if (!$request->hasFile('document_file')) {
                return response()->json(array('errors' => 'Select your file'));
            } else {

                $allowedfileExtension = ['jpg', 'png', 'gif', 'pdf', 'jpeg', 'doc', 'docx', 'xls', 'xlsx'];
                $files = $request->file('document_file');

                $extension = $files->getClientOriginalExtension();

                $check = in_array($extension, $allowedfileExtension);

                if ($check) {
                    $path = public_path() . '/assets/documents';
                    $files->move($path, $files->getClientOriginalName());
                    // $path = $request->image->store('public/uploads/app_sliders');
                    $file = $request->document_file->getClientOriginalName();
                    //  exit;
                    //store image file into directory and db
                    $save = new Document();
                    // $save->title = $name;
                    $save->ins_id = $id;
                    $save->name = $request->input('document_name');
                    $save->file = $file;
                    $save->save();
                    return response()->json('Document Saved Successfully.');
                } else {
                    return response()->json('Please check your file extention and document name.');
                }
            }
        } else {
            return response()->json('Please check your file extention and document name.');
        }
    }



    //*** GET Request Delete
    public function destroy($id)
    {
        if ($id == 1) {
            return "You don't have access to remove this admin";
        }
        $data = Admin::findOrFail($id);
        //If Photo Doesn't Exist
        if ($data->photo == null) {
            $data->delete();
            //--- Redirect Section
            $msg = 'Data Deleted Successfully.';
            return response()->json($msg);
            //--- Redirect Section Ends
        }
        //If Photo Exist
        @unlink('assets/images/' . $data->photo);
        $data->delete();
        $data->delete();
        //--- Redirect Section
        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }
}
