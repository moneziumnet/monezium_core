<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use App\Models\User;
use App\Models\Order;
use App\Models\Follow;
use App\Models\Rating;
use App\Models\Wallet;
use App\Models\UserDps;
use App\Models\UserFdr;
use App\Models\Currency;
use App\Models\UserLoan;
use App\Models\Wishlist;
use App\Models\Withdraw;
use App\Models\OrderedItem;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Models\UserDocument;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use App\Models\Transaction;

class UserController extends Controller
{
    public function __construct()
        {
            $this->middleware('auth:admin');
        }

        public function datatables()
        {
             $datas = User::orderBy('id','desc');

             return Datatables::of($datas)
                                ->addColumn('action', function(User $data) {
                                    return '<div class="btn-group mb-1">
                                        <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        '.'Actions' .'
                                        </button>
                                        <div class="dropdown-menu" x-placement="bottom-start">
                                        <a href="' . route('admin-user-profile',$data->id) . '"  class="dropdown-item">'.__("Profile").'</a>
                                        <a href="' . route('admin-user-edit',$data->id) . '" class="dropdown-item" >'.__("Edit").'</a>
                                        <a href="javascript:;" class="dropdown-item send" data-email="'. $data->email .'" data-toggle="modal" data-target="#vendorform">'.__("Send").'</a>
                                        <a href="javascript:;" data-toggle="modal" data-target="#deleteModal" class="dropdown-item" data-href="'.  route('admin-user-delete',$data->id).'">'.__("Delete").'</a>
                                        </div>
                                    </div>';
                                })

                                ->addColumn('status', function(User $data) {
                                    $status      = $data->is_banned == 1 ? __('Block') : __('Unblock');
                                    $status_sign = $data->is_banned == 1 ? 'danger'   : 'success';

                                        return '<div class="btn-group mb-1">
                                        <button type="button" class="btn btn-'.$status_sign.' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            '.$status .'
                                        </button>
                                        <div class="dropdown-menu" x-placement="bottom-start">
                                            <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin-user-ban',['id1' => $data->id, 'id2' => 0]).'">'.__("Unblock").'</a>
                                            <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin-user-ban',['id1' => $data->id, 'id2' => 1]).'">'.__("Block").'</a>
                                        </div>
                                        </div>';
                                })

                                ->addColumn('verify', function(User $data) {
                                    $status      = $data->email_verified == 'Yes' ? __('Yes') : __('No');
                                    $status_sign = $data->email_verified == 'No' ? 'danger'   : 'success';

                                        return '<div class="btn-group mb-1">
                                        <button type="button" class="btn btn-'.$status_sign.' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            '.$status .'
                                        </button>
                                        <div class="dropdown-menu" x-placement="bottom-start">
                                            <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin-user-verify',['id1' => $data->id, 'id2' => 'Yes']).'">'.__("Yes").'</a>
                                            <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin-user-verify',['id1' => $data->id, 'id2' => 'No']).'">'.__("No").'</a>
                                        </div>
                                        </div>';
                                })
                                ->rawColumns(['action','status', 'verify'])
                                ->toJson();
        }

        public function index()
        {
            return view('admin.user.index');
        }

        //*** GET Request
    public function create()
    {
        return view('admin.user.create');
    }

            //*** POST Request
    public function store(Request $request)
    {
        $rules = [
            'name'=> 'required',
            'email' => 'required|unique:users',
            'photo' => 'required|mimes:jpeg,jpg,png,svg',            
            'password'=> 'required',
            'phone'=> 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
        return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
       //--- Validation Section Ends

        //--- Logic Section
        $data = new User();
        $input = $request->all();
        if ($file = $request->file('photo'))
        {
            $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/images',$name);
            $input['photo'] = $name;
        }

        $input['password'] = bcrypt($request['password']);
        $data->fill($input)->save();
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = __('New Data Added Successfully.').'<a href="'.route('admin.user.index').'">'.__('View Lists.').'</a>';;

        return response()->json($msg);
        //--- Redirect Section Ends
 }



        public function image()
        {
            return view('admin.generalsetting.user_image');
        }

        public function profileInfo($id)
        {
            $data = User::findOrFail($id);
            $data['loans'] = UserLoan::whereUserId($data->id)->get();
            $data['dps'] = UserDps::whereUserId($data->id)->get();
            $data['fdr'] = UserFdr::whereUserId($data->id)->get();
            $data['withdraws'] = Withdraw::whereUserId($data->id)->get();
            $data['data'] = $data;
            return view('admin.user.profile',$data);
        }

        public function profileAccounts($id)
        {
            $data = User::findOrFail($id);
            $wallets = Wallet::where('user_id',$id)->with('currency')->get();
            $data['wallets'] = $wallets;
            $data['data'] = $data;
            return view('admin.user.profileaccounts',$data);
        }
 

        public function profileDocuments($id)
        {
            //$data = Generalsetting::first();
            $user = User::findOrFail($id);
            $documents = UserDocument::where('user_id',$user->id)->get();
            $data['documents'] = $documents;
            $data['data'] = $user;
            return view('admin.user.profiledocuments',$data);
        }

        public function createfile($id)
        {
            $user = User::findOrFail($id);
           // dd($user);
            $data['data'] = $user;
            return view('admin.user.addprofiledocuments',$data);
        }

        public function storefile(Request $request,$id)
        {
            if ($request->isMethod('post')) {
                $rules = [
                    'document_name'   => 'required',
                    'document_file'   => 'required'
                ];
    
                $validator = Validator::make($request->all(), $rules);
    
                if ($validator->fails()) {
                    return redirect()->back()->with('unsuccess','Select Valid file for upload'); 
                }
    
                if (!$request->hasFile('document_file')) {
                    return redirect()->back()->with('unsuccess','Select Valid file for upload'); 
                } else {
    
                    $allowedfileExtension = ['jpg', 'png', 'gif', 'pdf', 'jpeg', 'doc', 'docx', 'xls', 'xlsx'];
                    $files = $request->file('document_file');
    
                    $extension = $files->getClientOriginalExtension();
    
                    $check = in_array($extension, $allowedfileExtension);
    
                    if ($check) {
                        $path = public_path() . '/assets/user_documents';
                        $files->move($path, $files->getClientOriginalName());
                        // $path = $request->image->store('public/uploads/app_sliders');
                        $file = $request->document_file->getClientOriginalName();
                        //  exit;
                        $user = User::findOrFail($id);
                        //store image file into directory and db
                        
                        $save = new UserDocument();
                        $save->user_id = $user->id;
                        $save->name = $request->input('document_name');
                        $save->file = $file;
                        $save->save();
                        return redirect()->back()->with('success','Document Saved Successfully.'); 
                    } else {
                        return redirect()->back()->with('unsuccess','Please check your file extention and document name.'); 
                    }
                }
            } else {
                return redirect()->back()->with('unsuccess','Please check your file extention and document name.'); 
            }
        }

        public function fileDownload($id)
        {
            $document = UserDocument::findOrFail($id);
    
            $file = public_path("assets/user_documents/" . $document->file);
            return Response::download($file);
        }

        public function fileDestroy($id)
        {
            $document = UserDocument::findOrFail($id);
    
            if (file_exists(public_path("assets/user_documents/" . $document->file))) {
                @unlink(public_path("assets/user_documents/" . $document->file));
            }
            $document->delete();
            //--- Redirect Section
            $msg = 'Document Has Been Deleted Successfully.';
            return redirect()->back()->with('success',$msg); 
            
        }

        public function profileSettings($id)
        {
            $data = User::findOrFail($id);
            $data['data'] = $data;
            return view('admin.user.profilesettings',$data);
        }

        public function profilePricingplan($id)
        {
            $data = User::findOrFail($id);
            $data['data'] = $data;
            return view('admin.user.profilepricingplan',$data);
        }

        public function profileTransctions($id)
        {
            $user = User::findOrFail($id);
            $data['data'] = $user;
            return view('admin.user.profiletransactions',$data);
        }

        public function profileBanks($id)
        {
            $data = User::findOrFail($id);
            $data['data'] = $data;
            return view('admin.user.profilebanks',$data);
        }

        public function trandatatables($id)
        {
            $datas = Transaction::where('user_id',$id)->orderBy('id','desc')->get();  
        
            return Datatables::of($datas)
                            ->editColumn('amount', function(Transaction $data) {
                                $currency = Currency::whereId($data->currency_id)->first();
                                return $data->type.amount($data->amount,$currency->type,2).$currency->code;
                            })
                            ->editColumn('created_at', function(Transaction $data) {
                                $date = date('d-m-Y',strtotime($data->created_at));
                                return $date;
                            })
                            ->editColumn('remark', function(Transaction $data) {
                                return ucwords(str_replace('_',' ',$data->remark));
                            })
                            ->editColumn('charge', function(Transaction $data) {
                                $currency = Currency::whereId($data->currency_id)->first();
                                return $data->type.amount($data->charge,$currency->type,2).$currency->code;
                            })
                            
                            ->rawColumns([''])
                            ->toJson();
        }

        public function profileModules($id)
        {
            $data = User::findOrFail($id);
            $data['data'] = $data;
            return view('admin.user.profilemodules',$data);
        }

        public function ban($id1,$id2)
        {
            $user = User::findOrFail($id1);
            $user->is_banned = $id2;
            $user->update();
            $msg = 'Data Updated Successfully.';
            return response()->json($msg);
        }

        public function verify($id1,$id2)
        {
            $user = User::findOrFail($id1);
            $user->email_verified = $id2;
            $user->update();
            $msg = 'Data Updated Successfully.';
            return response()->json($msg);
        }


        public function changePassword(Request $request, $id)
        {
            $user = User::findOrFail($id);
            if ($request->newpass == $request->renewpass){
                $input['password'] = bcrypt($request['password']);
            }else{
                return redirect()->back()->with('unsuccess','Confirm password does not match.');
            }

            $user->update($input);
            return redirect()->back()->with('success','Password Successfully Changed.'); 
        }

        public function updateModules(Request $request, $id)
        {
            $user = User::findOrFail($id);
            if (!empty($request->section)) {
                $input['section'] = implode(" , ", $request->section);
            } else {
                $input['section'] = '';
            }
            
            $user->update($input);

            $msg = __('Password Successfully Changed.');
            return response()->json($msg);
        }

        public function edit($id)
        {
            $data = User::findOrFail($id);
            //dd($data);
            return view('admin.user.edit',compact('data'));
        }


        public function update(Request $request, $id)
        {
            $rules = [
                   'photo' => 'mimes:jpeg,jpg,png,svg',
                    ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
              return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }

            $user = User::findOrFail($id);
            $data = $request->all();
            
            if ($file = $request->file('photo'))
            {
                $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
                $file->move('assets/images',$name);
                if($user->photo != null)
                {
                    if (file_exists(public_path().'/assets/images/'.$user->photo)) {
                        unlink(public_path().'/assets/images/'.$user->photo);
                    }
                }
                $data['photo'] = $name;
            }
            if (!empty($request->input('user_type'))) {
                $data['user_type'] = implode(',',$request->input('user_type'));
            }
//dd($data);
            $user->update($data);
            $msg = 'Customer Information Updated Successfully.';
            return response()->json($msg);
        }

        public function adddeduct(Request $request){
            $user = User::whereId($request->user_id)->first();
            if($user){
                if($request->type == 'add'){
                    // $user->increment('balance',$request->amount);
                    $currency_id = Currency::whereIsDefault(1)->first()->id;
                    user_wallet_increment($user->id, $currency_id, $request->amount);
                    return redirect()->back()->with('message','User balance added');
                }else{
                    $currency = Currency::whereIsDefault(1)->first()->id;
                    $userBalance = user_wallet_balance($user->id, $currency);
                    if($userBalance>=$request->amount){
                        user_wallet_decrement($user->id, $currency,$request->amount);
                       // $user->decrement('balance',$request->amount);
                        return redirect()->back()->with('message','User balance deduct!');
                    }else{
                        return redirect()->back()->with('warning','User don,t have sufficient balance!');
                    }
                }
            }else{
                return redirect()->back()->with('warning','User not found!');
            }
        }

        public function withdraws(){
            return view('admin.user.withdraws');
        }

          public function withdrawdatatables()
          {
               $datas = Withdraw::orderBy('id','desc');

               return Datatables::of($datas)
                                  ->addColumn('email', function(Withdraw $data) {
                                      $email = $data->user->email;
                                      return $email;
                                  })
                                  ->addColumn('phone', function(Withdraw $data) {
                                    $phone = $data->user->phone;
                                    return $phone;
                                })
                                ->editColumn('status', function(Withdraw $data) {
                                    $status = ucfirst($data->status);
                                    return $status;
                                })

                                  ->editColumn('amount', function(Withdraw $data) {
                                      $amount = $data->amount;
                                      return '$' . $amount;
                                  })
                                  ->editColumn('created_at', function(Withdraw $data) {
                                    $date = $data->created_at->diffForHumans();
                                    return $date;
                                })


                               ->addColumn('action', function(Withdraw $data) {

                                if($data->status == "pending") {
                                    $action = '<a href="javascript:;" data-href="' . route('admin-withdraw-accept',$data->id) . '"  class="dropdown-item" data-toggle="modal"  data-target="#status-modal">'.__("Accept").'</a>
                                    <a href="javascript:;" data-href="' . route('admin-withdraw-reject',$data->id) . '"  class="dropdown-item" data-toggle="modal" data-target="#confirm-delete">'.__("Reject").'</a>
                                ';
                                }else{
                                    $action = '';
                                }
                                return '<div class="btn-group mb-1">
                                <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                  '.'Actions' .'
                                </button>
                                <div class="dropdown-menu" x-placement="bottom-start">
                                  <a href="javascript:;" data-href="' . route('admin.withdraw.show',$data->id) . '"  class="dropdown-item" id="applicationDetails" data-toggle="modal" data-target="#details">'.__("Details").'</a>'.$action.'

                                </div>
                              </div>';
                             })
                            ->rawColumns(['name','email','amount','action'])
                            ->toJson();
          }


    public function withdrawdetails($id)
    {
        $withdraw = Withdraw::findOrFail($id);
        return view('admin.user.withdraw-details',compact('withdraw'));
    }


    public function accept($id)
    {
        $withdraw = Withdraw::findOrFail($id);
        $data['status'] = "completed";
        $withdraw->update($data);

        $msg = __('Withdraw Accepted Successfully.');
        return response()->json($msg);

    }


    public function reject($id)
    {
        $withdraw = Withdraw::findOrFail($id);
        $currency = Currency::whereIsDefault(1)->first()->id;
        $account = User::findOrFail($withdraw->user->id);
        $amount =  $withdraw->amount + $withdraw->fee;
        user_wallet_increment($account->id, $currency, $amount);
        // $account->balance = $account->balance + $withdraw->amount + $withdraw->fee;
        // $account->update();
        $data['status'] = "rejected";
        $withdraw->update($data);

        $msg = __('Withdraw Rejected Successfully.');
        return response()->json($msg);
    }

    public function destroy($id)
        {
            $user = User::findOrFail($id);

             if($user->transactions->count() > 0)
            {
                foreach ($user->transactions as $transaction) {
                    $transaction->delete();
                }
            }

            if($user->withdraws->count() > 0)
            {
                foreach ($user->withdraws as $withdraw) {
                    $withdraw->delete();
                }
            }

            if($user->deposits->count() > 0)
            {
                foreach ($user->deposits as $deposit) {
                    $deposit->delete();
                }
            }

            if($user->wiretransfers->count() > 0)
            {
                foreach ($user->wiretransfers as $transfer) {
                    $transfer->delete();
                }
            }

            if($user->loans->count() > 0)
            {
                foreach ($user->loans as $loan) {
                    $loan->delete();
                }
            }

            if($user->dps->count() > 0)
            {
                foreach ($user->dps as $dps) {
                    $dps->delete();
                }
            }

            if($user->fdr->count() > 0)
            {
                foreach ($user->fdr as $fdr) {
                    $fdr->delete();
                }
            }

            if($user->balanceTransfers->count() > 0)
            {
                foreach ($user->balanceTransfers as $balanceTransfer) {
                    $balanceTransfer->delete();
                }
            }

                @unlink('/assets/images/'.$user->photo);
                $user->delete();
                
                $msg = 'Data Deleted Successfully.';
                return response()->json($msg);       
        }

}
