<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use App\Models\User;
use App\Models\Order;
use App\Models\Follow;
use App\Models\Rating;
use App\Models\Wallet;
use App\Models\Plan;
use App\Models\Charge;
use App\Models\UserDps;
use App\Models\UserFdr;
use App\Models\Currency;
use App\Models\UserLoan;
use App\Models\Wishlist;
use App\Models\Withdraw;
use App\Models\OrderedItem;
use App\Models\Transaction;
use App\Models\Withdrawals;
use Illuminate\Support\Str;
use App\Models\UserDocument;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Exports\AdminExportTransaction;
use App\Models\BankPlan;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;


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
            $data['withdraws'] = Withdrawals::whereUserId($data->id)->get();
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

        public function profilewallets($id, $wallet_type, $currency_id)
        {
            if($wallet_type == 0) {
                $wallets = Wallet::where('user_id',$id)->where('user_id',$id)->with('currency')->get();
            }
            else {
                $wallets = Wallet::where('user_id', $id)->where('wallet_type', $wallet_type)->where('currency_id', $currency_id)->with('currency')->get();
            }
            return $wallets;
        }

        public function profilewalletcreate($id, $wallet_type, $currency_id)
        {
            {
                $wallet = Wallet::where('user_id', $id)->where('wallet_type', $wallet_type)->where('currency_id', $currency_id)->first();
                $gs = Generalsetting::first();
                if(!$wallet)
                {
                  $user_wallet = new Wallet();
                  $user_wallet->user_id = $id;
                  $user_wallet->user_type = 1;
                  $user_wallet->currency_id = $currency_id;
                  $user_wallet->balance = 0;
                  $user_wallet->wallet_type = $wallet_type;
                  $user_wallet->wallet_no =$gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999);
                  $user_wallet->created_at = date('Y-m-d H:i:s');
                  $user_wallet->updated_at = date('Y-m-d H:i:s');
                  $user_wallet->save();
                  $msg = __('Account New Wallet Updated Successfully.');
                  return response()->json($msg);
                }
                else {
                    return response()->json(array('errors' => [0 =>'This wallet has already been created.']));
                }

              }
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

                    //$allowedfileExtension = ['jpg', 'png', 'gif', 'pdf', 'jpeg', 'doc', 'docx', 'xls', 'xlsx'];
                    $allowedfileExtension = ['pdf'];  // ['jpg', 'png', 'gif', 'pdf', 'jpeg', 'doc', 'docx', 'xls', 'xlsx'];
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

        public function fileView($id)
        {
            $document = UserDocument::findOrFail($id);

            // $file = public_path("assets/user_documents/" . $document->file);
            // return Response::download($file);

            return response()->file("assets/user_documents/" . $document->file, [
                'Content-Disposition' => 'inline; filename="'. $document->file .'"'
              ]);
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
            $plans = BankPlan::where('id','!=',$data->bank_plan_id)->get();
            $plan = BankPlan::findOrFail($data->bank_plan_id);
            //dd($plan);
            $data['data'] = $data;
            $data['plan'] = $plan;
            $data['plans'] = $plans;
            return view('admin.user.profilepricingplan',$data);
        }

        public function upgradePlan(Request $request, $id)
        {
            $rules = [
                'subscription_type' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
            $currency_id = Currency::whereIsDefault(1)->first()->id;
            $userBalance = user_wallet_balance($id,$currency_id);
            $subscription_type_id = $request->input('subscription_type');
            $plan = BankPlan::findOrFail($subscription_type_id);

            if($plan->amount > $userBalance)
            {
                return response()->json(array('errors' => 'Customer Balance not Available.'));
            }

            $trnx              = new Transaction();
            $trnx->trnx        = str_rand();
            $trnx->user_id     = $id;
            $trnx->user_type   = 1;
            $trnx->currency_id = $currency_id;
            $trnx->amount      = $plan->amount;
            $trnx->charge      = 0;
            $trnx->remark      = 'upgrade_plan';
            $trnx->type        = '-';
            $trnx->details     = trans('Upgrade Plan');
            $trnx->save();
            user_wallet_decrement($id, $currency_id, $plan->amount);

            $user = User::findorFail($id);
            if ($user) {

                $user->bank_plan_id = $subscription_type_id;
                $user->plan_end_date = $user->plan_end_date->addDays($plan->days);
                $user->update();
            }

            return response()->json(array('success' => 'Customer\'s Plan Upgrade Successfully.'));
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

        public function transctionEdit($id)
        {
            $transaction            = Transaction::findOrFail($id);
            $user                   = User::findOrFail($transaction->user_id);
            $data['data']           = $user;
            $data['transaction']    = $transaction;
            return view('admin.user.transctionEdit',$data);
        }

        public function transctionUpdate(Request $request, $id)
        {
            if($request->isMethod('POST'))
            {

                $rules = [
                    'transaction_date' => 'required',
                    'trnx' => 'required',
                    'description' => 'required',
                    'remark' => 'required',
                    'amount' => 'required'
                ];

                $validator = Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
                }
                $trnx            = Transaction::findOrFail($id);

                $currency_id = Currency::whereIsDefault(1)->first()->id;
                $userBalance = user_wallet_balance($trnx->user_id,$currency_id);
                $amount = $request->input('amount');
                $charge = $request->input('charge');
                $newTotal = $amount + $charge;

                if($trnx->type == "-")
                {
                    $totalAmt = $trnx->amount + $trnx->charge;
                    $balance = $userBalance + $totalAmt;

                    if($amount > $balance)
                    {
                        return response()->json(array('errors' => 'Customer Balance not Available.'));
                    }
                    user_wallet_increment($trnx->user_id, $currency_id, $totalAmt);
                    $trnx->currency_id = $currency_id;
                    $trnx->amount      = $amount;
                    $trnx->charge      = $charge;
                    $trnx->remark      = $request->input('remark');
                // $trnx->type        = '-';
                    $trnx->details     = $request->input('description');
                    $trnx->save();

                    user_wallet_decrement($trnx->user_id, $currency_id, $newTotal);
                    return response()->json(array('success' => 'Transacton Update Success'));
                }

                if($trnx->type == "+")
                {
                    $totalAmt = $trnx->amount + $trnx->charge;
                    $balance = $userBalance - $totalAmt;
                    user_wallet_decrement($trnx->user_id, $currency_id, $totalAmt);

                    $trnx->currency_id = $currency_id;
                    $trnx->amount      = $amount;
                    $trnx->charge      = $charge;
                    $trnx->remark      = $request->input('remark');
                // $trnx->type        = '-';
                    $trnx->details     = $request->input('description');
                    $trnx->save();
                    user_wallet_increment($trnx->user_id, $currency_id, $amount);

                    return response()->json(array('success' => 'Transacton Update Success'));
                }



                // $trnx->trnx        = str_rand();
                // $trnx->user_id     = $id;
                // $trnx->user_type   = 1;


            }else{
                return response()->json(array('errors' => 'Should be correct button click.'));
            }

        }

        public function profileTransctionsDetails($id)
        {
            $user = User::findOrFail($id);
            $data['data'] = $user;
            return view('admin.user.profiletransactions',$data);
        }

        public function trxDetails($id)
        {
            $transaction = Transaction::where('id',$id)->first();
            $transaction->currency = Currency::whereId($transaction->currency_id)->first();
            if(!$transaction){
                return response('empty');
            }
            return view('admin.user.trx_details',compact('transaction'));
        }

        public function transactionPDF($user_id)
        {
            //return (new AdminExportTransaction($user_id))->download('transaction.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
            return Excel::download( new AdminExportTransaction($user_id), 'transaction.pdf',\Maatwebsite\Excel\Excel::DOMPDF);
        }

        public function transactionExport($user_id)
        {
            return Excel::download( new AdminExportTransaction($user_id), 'transaction.xlsx');
        }

        public function trandatatables($id)
        {
            $datas = Transaction::where('user_id',$id)->orderBy('id','desc')->get();

            return Datatables::of($datas)
                            ->editColumn('amount', function(Transaction $data) {
                                $currency = Currency::whereId($data->currency_id)->first();
                                return $data->type.amount($data->amount,$currency->type,2).$currency->code;
                            })
                            ->editColumn('trnx', function(Transaction $data) {
                                $trnx = $data->trnx;
                                return $trnx;
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
                            ->addColumn('action', function (Transaction $data) {
                                return ' <a href="javascript:;"  data-href="" onclick="getDetails('.$data->id.')" class="detailsBtn" >
                                ' . __("Details") . '</a>';
                            })

                            ->rawColumns(['action'])
                            ->toJson();
        }


        public function profilePricingplandatatables($id)
        {
            $user = User::findOrFail($id);
            $globals = Charge::where('plan_id', $user->bank_plan_id)->get();
            $datas = $globals;
            return Datatables::of($datas)
                            ->editColumn('name', function(Charge $data) {
                                return $data->name;
                            })
                            ->editColumn('percent', function(Charge $data)  {
                                if ($data->data){
                                    return $data->data->percent_charge;
                                }
                                else {
                                    return 0;
                                }
                            })
                            ->editColumn('fixed', function(Charge $data) {
                                if ($data->data){
                                    return $data->data->fixed_charge;
                                }
                                else {
                                    return 0;
                                }
                            })
                            ->editColumn('percent_customer', function(Charge $data) use($id) {
                                $customplan =  Charge::where('user_id',$id)->where('name', $data->name)->first();
                                if ($customplan){
                                    return $customplan->data->percent_charge;
                                }
                                else {
                                    return 0;
                                }
                            })
                            ->editColumn('fixed_customer', function(Charge $data) use($id) {
                                $customplan =  Charge::where('user_id',$id)->where('name', $data->name)->first();

                                if ($customplan){
                                    return $customplan->data->fixed_charge;
                                }
                                else {
                                    return 0;
                                }
                            })
                            ->addColumn('action', function (Charge $data) use($id) {
                                $customplan =  Charge::where('user_id',$id)->where('name', $data->name)->first();

                                if($customplan) {
                                    return '<button type="button" class="btn btn-primary btn-big btn-rounded " onclick="getDetails('.$customplan->id.')" aria-haspopup="true" aria-expanded="false">
                                    Edit
                                    </button>';
                                }
                                else {

                                        return '<button type="button" class="btn btn-primary btn-big btn-rounded " data-id="'.$data->name.'" onclick="createDetails(\''.$data->name.'\')" aria-haspopup="true" aria-expanded="false">
                                        Edit
                                        </button>';
                                }
                            })

                            ->rawColumns(['action'])
                            ->toJson();
        }

        public function profilePricingplanedit($id) {
            $plandetail = Charge::findOrFail($id);
            return view('admin.user.profilepricingplanedit',compact('plandetail'));
        }

        public function profilePricingplancreate($id, $name) {
            $plandetail = new Charge();
            $plandetail->name = $name;
            $plandetail->user_id = $id;
            $plandetail->plan_id = 0;
            switch ($name) {
                case 'Transfer Money':
                    $plandetail->data = json_decode('{"percent_charge":"2","fixed_charge":"2","minimum":"10","maximum":"1000","daily_limit":"2000","monthly_limit":"5000"}');
                    $plandetail->slug = 'transfer-money-0-'.$id;
                    break;
                case 'Exchange Money':
                    $plandetail->data = json_decode('{"percent_charge":"2","fixed_charge":"2","minimum":"10","maximum":"1000"}');
                    $plandetail->slug = 'exchange-money-0-'.$id;
                    break;
                case 'Request Money':
                    $plandetail->data = json_decode('{"percent_charge":"1","fixed_charge":"2","minimum":"10","maximum":"2000"}');
                    $plandetail->slug = 'request-money-0-'.$id;
                    break;
                case 'Merchant Payment':
                    $plandetail->data = json_decode('{"percent_charge":"5","fixed_charge":"2"}');
                    $plandetail->slug = 'merchant-payment-0-'.$id;
                    break;
                case 'Create Voucher':
                    $plandetail->data = json_decode('{"percent_charge":"2","fixed_charge":"2","minimum":"10","maximum":"2000","commission":"10"}');
                    $plandetail->slug = 'create-voucher-0-'.$id;
                    break;
                case 'Create Invoice':
                    $plandetail->data = json_decode('{"percent_charge":"5","fixed_charge":"2"}');
                    $plandetail->slug = 'create-invoice-0-'.$id;
                    break;
                case 'Make Escrow':
                    $plandetail->data = json_decode('{"percent_charge":"5","fixed_charge":"2"}');
                    $plandetail->slug = 'make-escrow-0-'.$id;
                    break;
                case 'API Merchant Payment':
                    $plandetail->data = json_decode('{"percent_charge":"5","fixed_charge":"2"}');
                    $plandetail->slug = 'api-payment-0-'.$id;
                    break;
                case 'Account Maintenance':
                    $plandetail->data = json_decode('{"percent_charge":"2","fixed_charge":"2","minimum":"10","maximum":"100000"}');
                    $plandetail->slug = 'account-maintenance-0-'.$id;
                    break;
                case 'Card Maintenance':
                    $plandetail->data = json_decode('{"percent_charge":"2","fixed_charge":"2","minimum":"10","maximum":"100000"}');
                    $plandetail->slug = 'card-maintenance-0-'.$id;
                    break;
                case 'Transaction 1':
                    $plandetail->data = json_decode('{"percent_charge":"1","fixed_charge":"2","from":"1","till":"5000"}');
                    $plandetail->slug = 'transaction-1-0-'.$id;
                    break;
                case 'Transaction 2':
                    $plandetail->data = json_decode('{"percent_charge":"1","fixed_charge":"2","from":"5001","till":"20000"}');
                    $plandetail->slug = 'transaction-2-0-'.$id;
                    break;
                case 'Transaction 3':
                    $plandetail->data = json_decode('{"percent_charge":"1","fixed_charge":"2","from":"20001","till":"50000"}');
                    $plandetail->slug = 'transaction-3-0-'.$id;
                    break;
                case 'Referral':
                    $plandetail->data = json_decode('{"percent_charge":"1","fixed_charge":"2", "referral":"10","invited":"5"}');
                    $plandetail->slug = 'Referral-0-'.$id;
                    break;
            }
            return view('admin.user.profilepricingplanedit',compact('plandetail'));
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
                $input['password'] = Hash::make($request->newpass);
            }else{
                return response()->json(array('errors' => [ 0 => "Confirm password does not match." ]));
            }
            $user->update($input);
            return response()->json('Password Successfully Changed.');
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
