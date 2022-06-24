<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use App\Models\RequestDomain;
use App\Models\Admin;
use App\Models\Tenant; 
use Auth;
use App\Http\Controllers\Controller;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Validator;

use Stancl\Tenancy\Database\Models\Domain;

class RequestDomainController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    //*** JSON Request
    public function datatables()
    {
        $datas = RequestDomain::orderBy('id', 'desc');
        //--- Integrating This Collection Into Datatables
        return Datatables::of($datas)
            ->editColumn('created_at', function (RequestDomain $data) {
                return $data->created_at->toDateString();
            })
            ->editColumn('status', function (RequestDomain $requestdomain) {
                if ($requestdomain->is_approved == 1) {
                    return '<span class="badge badge-success">' . __('Active') . '</span>';
                } elseif ($requestdomain->is_approved == 2) {
                    return '<span class="badge badge-danger">' . __('Inactive') . '</span>';
                } else {
                    return '<span class="badge badge-warning">' . __('pedning') . '</span>';
                }
            })
            ->addColumn('action', function (RequestDomain $data) {
                return '<div class="btn-group mb-1">
                              <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                ' . 'Actions' . '
                              </button>
                              <div class="dropdown-menu" x-placement="bottom-start">
                                <a href="' . route('admin.requestdomain.edit', $data->id) . '"  class="dropdown-item">' . __("Edit") . '</a>
                                <a href="' . route('admin.requestdomain.approve.status', $data->id) . '"  class="dropdown-item">' . __("Approved") . '</a>
                                <a href="javascript:;" data-toggle="modal" data-target="#deleteModal" class="dropdown-item" data-href="' .  route('admin.requestdomain.delete', $data->id) . '">' . __("Delete") . '</a>
                              </div>
                            </div>';
            })
            ->rawColumns(['action', 'status'])
            ->toJson(); //--- Returning Json Data To Client Side
            // <a href="javascript:void(0)" data-action="/request-domain/disapprove/{{ $requestdomain->id }}" class="dropdown-item">' . __("Disapprove") . '</a>
    }


    //*** GET Request
    public function index()
    {
        return view('admin.requestdomain.index');
    }

    //*** GET Request
    public function create()
    {
        return view('admin.requestdomain.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:admins,email,',
                'domains' => 'required|unique:domains,domain',
                'password' => 'same:password_confirmation',

            ]
        );
        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $domain = new RequestDomain();
        $domain->name = $request->name;
        $domain->email = $request->email;
        $domain->password = Hash::make($request->password);
        $domain->domain_name = $request->domains;
        $domain->type = 'Admin';
        $domain->save();


        $msg = __('New Data Added Successfully.').'<a href="'.route('admin.requestdomain.index').'">'.__('View Lists.').'</a>';;
        return response()->json($msg);
    }

    public function approveStatus($id)
    {
        // dd($id);
        $data = RequestDomain::findOrFail($id);
        if ($data->is_approved == 0) {
            return view('admin.requestdomain.edit', compact('data'));
        } else {
            return redirect()->back();
        }
    }
    public function disapproveStatus($id)
    {
        $data = RequestDomain::findOrFail($id);
        if ($data->is_approved == 0) {
            $view =   view('admin.requestdomain.reason', compact('data'));
            return ['html' => $view->render()];
        } else {
            return redirect()->back();
        }
    }

    // public function updateStatus(Request $request, $id)
    // {
    //     $validator = Validator::make(
    //         $request->all(),
    //         [
    //             'reason' => 'required',
    //         ]
    //     );
    //     if ($validator->fails()) {
            // return response()->json(array('errors' => $validator->getMessageBag()->toArray()));    
    //     }

    //     $requestdomain = RequestDomain::find($id);
    //     $requestdomain->reason = $request->reason;
    //     $requestdomain->is_approved = 2;
    //     $requestdomain->update();
    //     return redirect()->back()->with('success', __('Domain Request Disapprove successfully'));
    // }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = RequestDomain::findOrFail($id);
        return view('admin.requestdomain.data_edit', compact('data'));
    }

    public function data_update(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email',
                'domains' => 'required|unique:domains,domain',
            ]
        );
        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $requestdomain = RequestDomain::find($id);

        $requestdomain['name'] = $request->name;
        $requestdomain['email'] = $request->email;
        $requestdomain['domain_name'] = $request->domains;
        // $requestdomain['password'] = Hash::make($request->password);
        if (!empty($request->password)) {
            $requestdomain->password = Hash::make($request->password);
        }
        $requestdomain->update();
        $msg = 'Domain Request updated successfully'.'<a href="'.route("admin.requestdomain.index").'">View Domain Lists</a>';
        return response()->json($msg);
    }

    public function destroy($id)
    {
        $requestdomain = RequestDomain::find($id);
        $requestdomain->delete();
        $msg =  __('Domain Request deleted successfully');
        return response()->json($msg);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $req = RequestDomain::where('email', $request->email)->first();

        // $data = Order::where('domainrequest_id', $req->id)->first();
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email',
                'role_id'=> 'required',
                'domains' => 'required|unique:domains,domain',
            ]
        );
        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        $input['name'] = $request->name;
        $input['email'] = $request->email;
        $input['password'] = $request->password;
        $input['role_id'] = $request->role_id;

        $admin = Admin::where('email', $request->email)->first();
        
        if ($admin){
            $admin->update($input);
        }
        else {
            $input['phone'] = '';
            $admin = Admin::create($input);
        }

        if (tenant('id') == null) {
            try {
                $tenant = Tenant::create([
                    'id' => $admin->id,
                    'tenancy_db_name' => $request->db_name,
                    'tenancy_db_username' => $request->db_username,
                    'tenancy_db_password' => $request->db_password,
                ]);
                Domain::create([
                    'domain' => $request->domains,
                    'tenant_id' => $tenant->id,
                ]);
                $admin->tenant_id = $tenant->id;
                $admin->status = 1;
                $admin->save();
            } catch (\Exception $e) {
                return response()->json(array('errors' => $e->getMessage()));
            }
        }
        
        $req->is_approved = 1;
        $req->save();
        $msg = 'Domain created successfully'.'<a href="'.route("admin.requestdomain.index").'">View Domain Lists</a>';
        return response()->json($msg);
    }

}
