<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use PDF;
use App\Models\User;
use App\Models\Order;
use App\Models\Follow;
use App\Models\Rating;
use App\Models\Wallet;
use App\Models\Plan;
use App\Models\KycForm;
use App\Models\Charge;
use App\Models\UserDps;
use App\Models\UserFdr;
use App\Models\Currency;
use App\Models\UserLoan;
use App\Models\Wishlist;
use App\Models\Withdraw;
use App\Models\OrderedItem;
use App\Models\Transaction;
use App\Models\LoginActivity;
use App\Models\Withdrawals;
use App\Models\SubInsBank;
use App\Models\CryptoDeposit;
use App\Models\BankGateway;
use App\Models\BankAccount;
use Illuminate\Support\Str;
use App\Models\UserDocument;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Exports\AdminExportTransaction;
use App\Models\BalanceTransfer;
use App\Models\BankPlan;
use App\Models\BankPoolAccount;
use App\Models\Beneficiary;
use App\Models\PlanDetail;
use App\Models\VirtualCard;
use App\Models\KycRequest;
use App\Models\ChLayer;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Auth;
use Illuminate\Contracts\Auth\Authenticatable as OtherAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Classes\BoxApi;

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
                ->addColumn('name', function(User $data) {
                    $name = $data->company_name ?? $data->name;
                    return $name;
                })
                ->editColumn('balance', function(User $data) {
                    $currency = Currency::findOrFail(defaultCurr());
                    return '<div clase="text-right">'.$currency->symbol.amount(userBalance($data->id), $currency->type, 2).'</div>';
                })
                ->addColumn('action', function(User $data) {
                    return '<div class="btn-group mb-1">
                        <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        '.'Actions' .'
                        </button>
                        <div class="dropdown-menu" x-placement="bottom-start">
                        <a href="' . route('admin-user-profile',$data->id) . '"  class="dropdown-item">'.__("Profile").'</a>
                        <a href="javascript:;" class="dropdown-item send" data-email="'. $data->email .'" data-toggle="modal" data-target="#vendorform">'.__("Send").'</a>
                        <a href="javascript:;" data-toggle="modal" data-target="#deleteModal" class="dropdown-item" data-href="'.  route('admin-user-delete',$data->id).'">'.__("Delete").'</a>
                        <a href="'.  route('admin-user-login',encrypt($data->id)).'" class="dropdown-item" target="_blank">'.__("Login").'</a>
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
                ->rawColumns(['name','action','status', 'verify', 'balance'])
                ->toJson();
        }
        public function login($id)
        {
            $user = User::findOrFail(decrypt($id));
            Auth::guard('web')->loginUsingId($user->id);
            $user->verified = 1;
            $user->update();
            return redirect()->route('user.dashboard');

        }

        public function index()
        {
            return view('admin.user.index');
        }

        //*** GET Request
    public function create()
    {
        $userType = 'user';
        $userForms = KycForm::where('id',1)->first();
        return view('admin.user.create',compact('userForms'));
    }

            //*** POST Request
    public function store(Request $request)
    {

        $rules = [
            'email'   => 'required|email|unique:users',
            'phone' => 'required',
            'password' => 'required||min:6|confirmed'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $gs = Generalsetting::first();
        $subscription = BankPlan::findOrFail(1);

        $user = new User;
        $input = $request->all();
        $input['bank_plan_id'] = $subscription->id;
        $input['plan_end_date'] = Carbon::now()->addDays($subscription->days);//Carbon::now()->addDays(365);
        $input['password'] = bcrypt($request['password']);
        $input['account_number'] = $gs->account_no_prefix . date('ydis') . random_int(100000, 999999);
        $token = md5(time() . $request->name . $request->email);
        $input['verification_link'] = $token;
        $input['referral_id'] = $request->input('reff')? $request->input('reff'):'0';
        $input['affilate_code'] = md5($request->name . $request->email);
        $input['name'] = trim($request->firstname)." ".trim($request->lastname);
        $input['dob'] = $request->customer_dob;
        $input['kyc_method'] = 'manual';
        $input['phone'] = preg_replace("/[^0-9]/", "", $request->phone);
        $userType = 'user';
        $userForms = KycForm::where('id', 1)->first();

        $requireInformations = [];
        if($userForms){
            foreach(json_decode($userForms->data) as $key=>$value){
                if($value->type == 1){
                    $requireInformations['text'][$key] = strtolower(str_replace(' ', '_', $value->label));
                }
                elseif($value->type == 3){
                    $requireInformations['textarea'][$key] = strtolower(str_replace(' ', '_', $value->label));
                }else{
                    $requireInformations['file'][$key] = strtolower(str_replace(' ', '_', $value->label));
                }
            }
        }


        $details = [];
        foreach($requireInformations as $key=>$infos){
            foreach($infos as $index=>$info){

                if($request->has($info)){
                    if($request->hasFile($info)){
                        if ($file = $request->file($info))
                        {
                           $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
                           $file->move('assets/images',$name);
                           $details[$info] = [$name,$key];
                        }
                    }else{
                        $details[$info] = [$request->$info,$key];
                    }
                }
            }
        }
        if(!empty($details)){
            $input['kyc_info'] = json_encode($details,true);
            $input['kyc_status'] = 1;
        }

        if ($file = $request->file('photo'))
        {
            $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/images',$name);
            $input['photo'] = $name;
        }


        if($request->form_select == 1) {
            $subscription = BankPlan::where('type', 'corporate')->where('keyword', $subscription->keyword)->first();
            $input['bank_plan_id'] = $subscription->id;
            $input['plan_end_date'] = Carbon::now()->addDays($subscription->days);
            $input['company_name'] = $request->company_name;
            $input['company_reg_no'] = $request->company_reg_no;
            $input['company_vat_no'] = $request->company_vat_no;
            $input['company_dob'] = $request->company_dob;
            $input['company_type'] = $request->company_type;
            $input['company_city'] = $request->company_city;
            $input['company_country'] = $request->company_country;
            $input['company_zipcode'] = $request->company_zipcode;
            $input['company_address'] = $request->company_address;
            $input['personal_code'] = null;
            $input['your_id'] = null;
            $input['issued_authority'] = null;
            $input['date_of_issue'] = null;
            $input['date_of_expire'] = null;
        }

        if($request->form_select == 0) {
            $input['personal_code'] = $request->personal_code;
            $input['your_id'] = $request->your_id;
            $input['issued_authority'] = $request->issued_authority;
            $input['date_of_issue'] = $request->date_of_issue;
            $input['date_of_expire'] = $request->date_of_expire;
            $input['company_type'] = null;
            $input['company_city'] = null;
            $input['company_country'] = null;
            $input['company_zipcode'] = null;
            $input['company_name'] = null;
            $input['company_reg_no'] = null;
            $input['company_vat_no'] = null;
            $input['company_dob'] = null;
            $input['company_address'] = null;
        }

        $user->fill($input)->save();

        $pre_sections = explode(" , ", $user->section);
        $sectionlist = ['Incoming', 'External Payments', 'Request Money', 'Transactions', 'Payments', 'Payment between accounts', 'Exchange Money'];
        foreach($sectionlist as $key=>$section){
            if (!$user->sectionCheck($section)) {
                $manualfee = Charge::where('user_id', $user->id )->where('plan_id', $user->bank_plan_id)->where('name', $section)->first();
                if(!$manualfee) {
                    $manualfee = Charge::where('user_id', 0)->where('plan_id', $user->bank_plan_id)->where('name', $section)->first();
                }
                if($manualfee && $manualfee->data->fixed_charge > 0) {
                    $trans = new Transaction();
                    $trans->trnx = str_rand();
                    $trans->user_id     = $user->id;
                    $trans->user_type   = 1;
                    $trans->currency_id = defaultCurr();
                    $trans->amount      = 0;
                    $trans_wallet = get_wallet($user->id, defaultCurr(), 1);
                    $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                    $trans->charge      = $manualfee->data->fixed_charge;
                    $trans->type        = '-';
                    $trans->remark      = 'module';
                    $trans->details     = $section.trans(' Section Create');
                    $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
                    $trans->save();

                    user_wallet_decrement($user->id, defaultCurr(), $manualfee->data->fixed_charge, 1);
                    user_wallet_increment(0, defaultCurr(), $manualfee->data->fixed_charge, 9);
                }
                array_push($pre_sections, $section);
            }
        }
        $modules = explode(" , ", $user->modules);
        foreach($sectionlist as $key=>$section) {
            if(!$user->moduleCheck($section)) {
                array_push($modules, $section);
            }
        }
        $user->modules= implode(" , ", $modules);
        $user->section= implode(" , ", $pre_sections);
        $user->update();
        $default_currency = Currency::where('is_default','1')->first();
        $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
        if(!$chargefee) {
            $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
        }

        $user_wallet = new Wallet();
        $user_wallet->user_id = $user->id;
        $user_wallet->user_type = 1;
        $user_wallet->currency_id = $default_currency->id;
        $user_wallet->balance = -1 * ($chargefee->data->fixed_charge + $subscription->amount);
        $user_wallet->wallet_type = 1;
        $user_wallet->wallet_no =$gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999);
        $user_wallet->created_at = date('Y-m-d H:i:s');
        $user_wallet->updated_at = date('Y-m-d H:i:s');
        $user_wallet->save();

        $trans = new Transaction();
        $trans->trnx = str_rand();
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id = $default_currency->id;
        $trans->amount      = 0;
        $trans_wallet       = get_wallet($user->id, $default_currency->id, 1);
        $trans->wallet_id   = $user_wallet->id;
        $trans->charge      = $chargefee->data->fixed_charge;
        $trans->type        = '-';
        $trans->remark      = 'account-open';
        $trans->details     = trans('Wallet Create');
        $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
        $trans->save();
        mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $default_currency->code, 'type'=>'Current', 'date_time'=> dateFormat($trans->created_at)], $user);


        $trans = new Transaction();
        $trans->trnx = str_rand();
        $trans->user_id     = $user->id;
        $trans->user_type   = 1;
        $trans->currency_id = $default_currency->id;
        $trans->amount      = $subscription->amount;
        $trans_wallet       = get_wallet($user->id, defaultCurr(), 1);
        $trans->wallet_id   = $user_wallet->id;
        $trans->charge      = 0;
        $trans->type        = '-';
        $trans->remark      = 'price_plan';
        $trans->details     = trans('Price Plan');
        $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
        $trans->save();



        $user->email_verified = 'Yes';
        $user->update();
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

        public function profileAccountDeposit(Request $request)
        {
            $wallet = Wallet::findOrFail($request->wallet_id);
            $trax_details = $request->except('_token', 'wallet_id');
            $trax_details['sender'] = $request->fullname;
            $trax_details['receiver'] = $wallet->user->company_name ?? $wallet->user->name;
            $trax_details = json_encode($trax_details, True);

            $user = User::findOrFail($wallet->user_id);
            $rate =  getRate($wallet->currency);
            $amount = $request->amount / $rate;
            $transaction_global_cost = 0;
            $transaction_global_fee = check_global_transaction_fee($amount, $user, 'deposit');
            if($transaction_global_fee)
            {
                $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($amount/100) * $transaction_global_fee->data->percent_charge;
            }
            $transaction_custom_cost = 0;
            if($user->referral_id != 0)
            {
                $transaction_custom_fee = check_custom_transaction_fee($amount, $user, 'deposit');
                if($transaction_custom_fee) {
                    $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($amount/100) * $transaction_custom_fee->data->percent_charge;
                }
                $remark = 'Deposit_supervisor_fee';
                if (check_user_type_by_id(4, $user->referral_id)) {
                    user_wallet_increment($user->referral_id, $wallet->currency_id, $transaction_custom_cost*$rate, 6);
                    $trans_wallet = get_wallet($user->referral_id, $wallet->currency_id, 6);
                }
                elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                    $remark = 'Deposit_manager_fee';
                    user_wallet_increment($user->referral_id, $wallet->currency_id, $transaction_custom_cost*$rate, 10);
                    $trans_wallet = get_wallet($user->referral_id, $wallet->currency_id, 10);
                }
                $referral_user = User::findOrFail($user->referral_id);

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->referral_id;
                $trans->user_type   = 1;
                $trans->currency_id = $wallet->currency_id;
                $trans->amount      = $transaction_custom_cost*$rate;

                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                $trans->charge      = 0;
                $trans->type        = '+';
                $trans->remark      = $remark;
                $trans->details     = trans('Deposit complete');

                $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.($referral_user->company_name ?? $referral_user->name).'"}';
                $trans->save();
            }
            $fee = $transaction_global_cost + $transaction_custom_cost;
            $final_amount = $amount - $fee;
            user_wallet_increment($wallet->user_id,$wallet->currency_id,$final_amount*$rate, $wallet->wallet_type);
            user_wallet_increment(0, $wallet->currency_id, $transaction_global_cost*$rate, 9);

            $trans = new Transaction();
            $trans->trnx = Str::random(4).time();
            $trans->user_id     = $wallet->user_id;
            $trans->user_type   = 1;
            $trans->wallet_id   = $wallet->id;
            $trans->currency_id = $wallet->currency_id;
            $trans->amount      = $request->amount;
            $trans->charge      = $fee*$rate;
            $trans->type        = '+';
            $trans->remark      = 'Deposit';
            $trans->details     = trans('Deposit complete');
            $trans->data        = $request->wallet_type == 'crypto' ? '{"sender":"'.$request->fullname.'", "receiver":"'.$request->adddress.'"}':$trax_details;
            $trans->save();
            if ($request->wallet_type == 'crypto') {
                $deposit = new CryptoDeposit();
                $input = $request->all();

                $deposit->fill($input)->save();
            }
            return redirect()->back()->with(array('message' => 'Deposit Create Successfully'));
        }

        public function profileAccountDepositForm() {
            return view('admin.user.walletdeposit');
        }

        public function profileAccountCryptoDepositForm($id) {
            $wallet = Wallet::where('id', $id)->first();
            $data['wallet_no'] = $wallet->wallet_no;
            $data['user_id'] = $wallet->user_id;
            $data['currency_id'] = $wallet->currency_id;
            return view('admin.user.walletcryptodeposit', $data);
        }

        public function profilewallets($id, $wallet_type, $currency_id)
        {
            if($wallet_type == 0) {
                $wallets = Wallet::where('user_id',$id)->where('user_id',$id)->with('currency')->get();
            }
            else {
                $wallets = Wallet::where('user_id', $id)->where('wallet_type', $wallet_type)->where('currency_id', $currency_id)->with('currency')->get();
                $currency = Currency::findOrFail($currency_id);
                if($currency->type == 2 && count($wallets) >= 1) {
                    $wallets[0]->balance = Crypto_Balance($wallets[0]->user_id, $wallets[0]->currency_id);
                }
            }
            return $wallets;
        }

        public function profilewalletcreate($id, $wallet_type, $currency_id)
        {
            {
                $wallet = Wallet::where('user_id', $id)->where('wallet_type', $wallet_type)->where('currency_id', $currency_id)->first();
                $currency =  Currency::findOrFail($currency_id);
                $gs = Generalsetting::first();
                $user = User::findOrFail($id);
                // return response()->json('$msg');
                if ($currency->type == 2) {

                    if ($currency->code == 'BTC') {
                        $keyword = str_rand();
                        $address = RPC_BTC_Create('createwallet',[$keyword]);
                    }
                    else if ($currency->code == 'ETH') {
                        $keyword = str_rand(6);
                        $address = RPC_ETH('personal_newAccount',[$keyword]);
                    }
                    else {
                        $eth_currency = Currency::where('code', 'ETH')->first();
                        $eth_wallet = Wallet::where('user_id', $id)->where('wallet_type', $wallet_type)->where('currency_id', $eth_currency->id)->first();
                        if (!$eth_wallet) {
                            return response()->json(array('errors' => [0 => __('You have to create Eth Crypto wallet firstly before create ERC20 token wallet.')]));
                        }
                        $address = $eth_wallet->wallet_no;
                        $keyword = $eth_wallet->keyword;
                    }
                    if ($address == 'error') {
                        return response()->json(array('errors' => [0 => __('You can not create this wallet because there is some issue in crypto node.')]));
                    }
                }
                else {
                    $address = $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999);
                    $keyword = '';
                }
                if(!$wallet)
                {

                  $user = User::findOrFail($id);

                  if($wallet_type == 2) {
                    if($currency->code != 'EUR') {
                        return response()->json(array('errors' => [0 => __('This api support only for EUR currency.')]));

                    }

                    $v_card = VirtualCard::where('user_id', $user->id)->where('currency_id', $currency_id)->first();
                    if($v_card) {
                        return response()->json(array('errors' => [0 => __($currency->code . ' Virtual Card already exists.')]));

                    }
                    $bankgateways = BankGateway::where('keyword', 'swan')->get();
                    if(count($bankgateways) > 1) {
                        foreach ($bankgateways as $key => $value) {
                            $bankaccount = BankAccount::where('user_id', $user->id)->where('currency_id', $currency_id)->where('subbank_id', $value->subbank_id)->first();
                            if($bankaccount) {
                                $account = $bankaccount;
                                $bankgateway = $value;
                            }
                        }
                    }
                    else {
                        return response()->json(array('errors' => [0 => __(' The api does not exist for creating Virtual Card.')]));
                    }
                    if(!$account) {
                        return response()->json(array('errors' => [0 => __('Please create swan Bank account before creating virtual card.')]));
                    }
                    $redirect_url = route('admin.dashboard');
                    $res = generate_card($bankgateway->information->client_id, $bankgateway->information->client_secret, $account->iban, $redirect_url);
                    if ($res[0] == 'error') {
                        return response()->json(array('errors' => [0 => __( $res[1])]));
                    }
                    if ($res[0] == 'success') {
                        Session::put('currency_id', $currency_id);
                        Session::put('user_id', $user->id);
                        return redirect()->away($res[1]);
                    }
                  }
                  else {
                    $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                    if(!$chargefee) {
                        $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                    }
                    $trans = new Transaction();
                    $trans->trnx = str_rand();
                    $trans->user_id     = $id;
                    $trans->user_type   = 1;
                    $trans->currency_id = defaultCurr();
                    $trans->amount      = 0;
                    $trans_wallet = get_wallet($id, defaultCurr(), 1);
                    $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                    $trans->charge      = $chargefee->data->fixed_charge;
                    $trans->type        = '-';
                    $trans->remark      = 'account-open';
                    $trans->details     = trans('Wallet Create');
                    $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
                    $trans->save();
                    $wallet_type_list = array('1'=>'Current', '2'=>'Card', '3'=>'Deposit', '4'=>'Loan', '5'=>'Escrow', '6'=>'Supervisor', '7'=>'Merchant', '8'=>'Crypto', '10'=>'Manager');
                    $currency = Currency::findOrFail(defaultCurr());

                    mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'type'=>$wallet_type_list[$wallet_type], 'date_time'=> dateFormat($trans->created_at)], $user);
                  }
                  $user_wallet = new Wallet();
                  $user_wallet->user_id = $id;
                  $user_wallet->user_type = 1;
                  $user_wallet->currency_id = $currency_id;
                  $user_wallet->balance = 0;
                  $user_wallet->wallet_type = $wallet_type;
                  $user_wallet->wallet_no =$address;
                  $user_wallet->keyword =$keyword;
                  $user_wallet->created_at = date('Y-m-d H:i:s');
                  $user_wallet->updated_at = date('Y-m-d H:i:s');
                  $user_wallet->save();

                  user_wallet_decrement($id, defaultCurr(), $chargefee->data->fixed_charge, 1);
                  user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);

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
            $documents = UserDocument::where('user_id',$user->id)->latest()->paginate(20);
            $data['documents'] = $documents;
            $data['data'] = $user;
            return view('admin.user.profiledocuments',$data);
        }

        public function createfile($id)
        {
            $user = User::findOrFail($id);
            $data['data'] = $user;
            $boxapi = new BoxApi();
            $check = $boxapi->api_check();
            if($check[0] == 'warning') {
                return redirect()->back()->with($check[0], $check[1]);
            }
            $data['access_token'] =$boxapi->basic_token()->access_token;
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

                        $save = new UserDocument();
                        $save->user_id = $id;
                        if($request->docu_type == 'type_file') {
                            $save->type = 'file';
                        }
                        else {
                            $save->type = 'folder';
                        }
                        $save->name = $request->document_name;
                        $save->file = $request->document_file;
                        $save->file_id = $request->file_id;
                        $save->save();
                return redirect()->route('admin-user-documents', $id)->with('message','Document Saved Successfully.');

            } else {
                return redirect()->route('admin-user-documents', $id)->with('warning','Please check your file extention and document name.');
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
            $box = new BoxApi();
            $check = $box->api_check();
            if($check[0] == 'warning') {
                return redirect()->back()->with($check[0], $check[1]);
            }
            if ($document->type == 'file') {
                $res = $box->download($document->file_id);
                $folderPath = 'assets/pdf/';
                $file = $folderPath.$document->file;
                file_put_contents($file, $res);
                return   response()->file("assets/pdf/" . $document->file, [
                    'Content-Disposition' => 'inline; filename="'. $document->file .'"'
                  ]);
            }
            else {
                $access_token = $box->basic_token()->access_token;
                $folder_id = $document->file_id;
                $data = User::findOrFail($document->user_id);
                return view('admin.user.documentpreview', compact('access_token', 'folder_id', 'data'));
            }

        }

        public function fileDestroy($id)
        {
            $document = UserDocument::findOrFail($id);

            if (file_exists(public_path("assets/pdf/" . $document->file))) {
                @unlink(public_path("assets/pdf/" . $document->file));
            }
            $document->delete();
            $msg = 'Document Has Been Deleted Successfully.';
            //--- Redirect Section
            return redirect()->back()->with('message',$msg);

        }

        public function profileSettings($id)
        {
            $data = User::findOrFail($id);
            $data['data'] = $data;
            $data['kycforms'] = KycForm::where('status', 1)->get();
            return view('admin.user.profilesettings',$data);
        }

        public function profilePricingplan($id)
        {
            $data = User::findOrFail($id);
            $type = $data->company_name ? 'corporate' : 'private';
            $plans = BankPlan::where('id','!=',$data->bank_plan_id)->where('type', $type)->get();
            $plan = BankPlan::findOrFail($data->bank_plan_id);
            //dd($plan);
            $data['data'] = $data;
            $data['plan'] = $plan;
            $data['plans'] = $plans;
            return view('admin.user.profilepricingplan',$data);
        }

        public function profilePricingplan_supervisor($id)
        {
            $data = User::findOrFail($id);
            $type = $data->company_name ? 'corporate' : 'private';
            $plans = BankPlan::where('id','!=',$data->bank_plan_id)->where('type', $type)->get();
            $plan = BankPlan::findOrFail($data->bank_plan_id);
            //dd($plan);
            $data['data'] = $data;
            $data['plan'] = $plan;
            $data['plans'] = $plans;
            return view('admin.user.profilepricingplansupervisor',$data);
        }

        public function profilePricingplan_manager($id)
        {
            $data = User::findOrFail($id);
            $type = $data->company_name ? 'corporate' : 'private';
            $plans = BankPlan::where('id','!=',$data->bank_plan_id)->where('type', $type)->get();
            $plan = BankPlan::findOrFail($data->bank_plan_id);
            //dd($plan);
            $data['data'] = $data;
            $data['plan'] = $plan;
            $data['plans'] = $plans;
            return view('admin.user.profilepricingplanmanager',$data);
        }

        public function upgradePlan(Request $request, $id)
        {
            $gs = Generalsetting::first();
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
                return response()->json(array('errors' =>[ 'Customer Balance not Available.']));
            }

            $trnx              = new Transaction();
            $trnx->trnx        = str_rand();
            $trnx->user_id     = $id;
            $trnx->user_type   = 1;
            $trnx->currency_id = $currency_id;
            $trnx->amount      = $plan->amount;
            $trans_wallet = get_wallet($id, $currency_id);
            $trnx->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
            $trnx->charge      = 0;
            $trnx->remark      = 'upgrade_plan';
            $trnx->type        = '-';
            $trnx->details     = trans('Upgrade Plan');
            $trnx->data        = '{"sender":"'.(User::findOrFail($id)->company_name ?? User::findOrFail($id)->name).'", "receiver":"'.$gs->disqus.'"}';
            $trnx->save();
            user_wallet_decrement($id, $currency_id, $plan->amount);
            user_wallet_increment(0, $currency_id, $plan->amount, 9);

            $user = User::findOrFail($id);
            if ($user) {

                $user->bank_plan_id = $subscription_type_id;
                $user->plan_end_date = $user->plan_end_date->addDays($plan->days);
                $user->update();
            }

            return response()->json('Customer\'s Plan Upgrade Successfully.');
        }

        public function profileTransctions($id)
        {
            $user = User::findOrFail($id);
            $data['data'] = $user;
            return view('admin.user.profiletransactions',$data);
        }

        public function walletTransctions($user_id, $wallet_id)
        {
            $data['wallet'] = Wallet::findOrFail($wallet_id);
            $user = User::findOrFail($user_id);
            $data['data'] = $user;
            return view('admin.user.wallettransactions',$data);
        }

        public function username_by_email(Request $request){
            if($data = User::where('email',$request->email)->first()){
                return ["name" => $data->company_name ?? $data->name, "phone" => $data->phone];
            }else{
                return false;
            }
        }

        public function username_by_phone(Request $request){
            if($data = User::where('phone',preg_replace("/[^0-9]/", "", $request->phone))->first()){
                return ["name" => $data->company_name ?? $data->name, "email" => $data->email];
            }else{
                return false;
            }
         }

        public function internal($user_id, $wallet_id)
        {
            $data['wallet'] = Wallet::findOrFail($wallet_id);
            $user = User::findOrFail($user_id);
            $data['data'] = $user;
            return view('admin.user.internal',$data);
        }

        public function internal_send(Request $request){
            $request->validate([
                'email'    => 'required',
                'wallet_id'         => 'required',
                'account_name'      => 'required',
                'amount'            => 'required|numeric|min:0',
                'description'       => 'required',
            ]);

            $wallet = Wallet::where('id',$request->wallet_id)->with('currency')->first();
            $user = User::find($wallet->user_id);

            if($user->bank_plan_id === null){
                return redirect()->back()->with('error','This user have to buy a plan to withdraw.');
            }

            if(now()->gt($user->plan_end_date)){
                return redirect()->back()->with('error','Plan Date Expired.');
            }

            $currency_id = $wallet->currency->id;
            $rate = getRate($wallet->currency);
            $dailySend = BalanceTransfer::whereUserId($user->id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus(1)->sum('amount');
            $monthlySend = BalanceTransfer::whereUserId($user->id)->whereMonth('created_at', '=', date('m'))->whereStatus(1)->sum('amount');
            $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'send')->first();

            if($dailySend > $global_range->daily_limit){
                return redirect()->back()->with('error','Daily send limit over.');
            }

            if($monthlySend > $global_range->monthly_limit){
                return redirect()->back()->with('error','Monthly send limit over.');
            }

            $gs = Generalsetting::first();

            if($request->email == $user->email){
                return redirect()->back()->with('error','Can not send money to himself!!');
            }

            if($request->amount < 0){
                return redirect()->back()->with('error','Request Amount should be greater than this!');
            }
            if($wallet->currency->type == 1) {
                if($request->amount > user_wallet_balance($user->id, $currency_id, $wallet->wallet_type)){
                    return redirect()->back()->with('error','Insufficient Balance.');
                }
            }
            else if ($wallet->currency->type == 2) {
                if($request->amount > Crypto_Balance($user->id, $currency_id, $wallet->wallet_type)){
                    return redirect()->back()->with('unsuccess','Insufficient Balance.');
                }
            }
            $transaction_global_cost = 0;
            if ($request->amount / $rate < $global_range->min || $request->amount / $rate > $global_range->max) {
                return redirect()->back()->with('error','Amount is not in defined range. Max value is '.$global_range->max.' and Min value is '.$global_range->min );
            }
            $transaction_global_fee = check_global_transaction_fee($request->amount/$rate, $user, 'send');
            if($transaction_global_fee)
            {
                $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/($rate*100)) * $transaction_global_fee->data->percent_charge;
            }
            $transaction_custom_cost = 0;
            if($user->referral_id != 0)
            {
                $transaction_custom_fee = check_custom_transaction_fee($request->amount/$rate, $user, 'send');
                if($transaction_custom_fee) {
                    $transaction_custom_cost = $transaction_custom_fee->data->fixed_charge + ($request->amount/($rate*100)) * $transaction_custom_fee->data->percent_charge;
                }
                $remark = 'Send_supervisor_fee';
                if ($wallet->currency->type == 1){
                    if (check_user_type_by_id(4, $user->referral_id)) {
                        user_wallet_increment($user->referral_id, $currency_id, $transaction_custom_cost*$rate, 6);
                        $trans_wallet = get_wallet($user->referral_id, $currency_id, 6);
                    }
                    elseif (DB::table('managers')->where('manager_id', $user->referral_id)->first()) {
                        $remark = 'Send_manager_fee';
                        user_wallet_increment($user->referral_id, $currency_id, $transaction_custom_cost*$rate, 10);
                        $trans_wallet = get_wallet($user->referral_id, $currency_id, 10);
                    }
                }
                else {
                    user_wallet_increment($user->referral_id, $currency_id, $transaction_custom_cost*$rate, 8);

                    $trans_wallet = get_wallet($user->referral_id, $currency_id, 8);
                    if($wallet->currency->code == 'ETH') {
                        RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                        $tx = '{"from": "'.$wallet->wallet_no.'", "to": "'.$trans_wallet->wallet_no.'", "value": "0x'.dechex($transaction_custom_cost*$rate*pow(10,18)).'"}';
                        RPC_ETH_Send('personal_sendTransaction',$tx, $wallet->keyword ?? '');
                    }
                    else if($wallet->currency->code == 'BTC') {
                        $res = RPC_BTC_Send('sendtoaddress',[$trans_wallet->wallet_no, amount($transaction_custom_cost*$rate, 2)],$wallet->keyword);
                        if (isset($res->error->message)){
                            return redirect()->back()->with(array('error' => __('Error: ') . $res->error->message));
                        }
                    }
                    else {
                        RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                        $tokenContract = $wallet->currency->address;
                        $result = erc20_token_transfer($tokenContract, $wallet->wallet_no, $trans_wallet->wallet_no, $transaction_custom_cost * $rate, $wallet->keyword);
                        if (json_decode($result)->code == 1){
                            return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
                        }
                    }
                }

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->referral_id;
                $trans->user_type   = 1;

                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                $trans->currency_id = $currency_id;
                $trans->amount      = $transaction_custom_cost*$rate;
                $trans->charge      = 0;
                $trans->type        = '+';
                $trans->remark      = $remark;
                $trans->details     = trans('Send Money');
                $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.(User::findOrFail($user->referral_id)->company_name ?? User::findOrFail($user->referral_id)->name).'", "description": "'.$request->description.'"}';
                $trans->save();
            }

            $finalCharge = $transaction_global_cost+$transaction_custom_cost;
            $finalamount =  $request->amount - $finalCharge*$rate;
            user_wallet_increment(0, $currency_id, $transaction_global_cost*$rate, 9);
            if ($wallet->currency->type == 2) {
                $towallet = get_wallet(0,$currency_id,9);

                if($wallet->currency->code == 'ETH') {
                    RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                    $tx = '{"from": "'.$wallet->wallet_no.'", "to": "'.$towallet->wallet_no.'", "value": "0x'.dechex($transaction_global_cost*$rate*pow(10,18)).'"}';
                    RPC_ETH_Send('personal_sendTransaction',$tx, $wallet->keyword ?? '');
                }
                else if($wallet->currency->code == 'BTC') {
                    $res = RPC_BTC_Send('sendtoaddress',[$towallet->wallet_no, amount($transaction_global_cost*$rate, 2)],$wallet->keyword);
                    if (isset($res->error->message)){
                        return redirect()->back()->with(array('error' => __('Error: ') . $res->error->message));
                    }
                }
                else {
                    RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                    $tokenContract = $wallet->currency->address;
                    $result = erc20_token_transfer($tokenContract, $wallet->wallet_no, $towallet->wallet_no, $transaction_global_cost*$rate, $wallet->keyword);
                    if (json_decode($result)->code == 1){
                        return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
                    }
                }
            }

            if($receiver = User::where('email',$request->email)->first()){

                $txnid = Str::random(4).time();
                $data = new BalanceTransfer();
                $data->user_id = $user->id;
                $data->receiver_id = $receiver->id;
                $data->transaction_no = $txnid;
                $data->currency_id = $request->wallet_id;
                $data->type = 'own';
                $data->cost = $finalCharge*$rate;
                $data->amount = $request->amount;
                $data->description = $request->description;
                $data->status = 1;
                $data->save();

                user_wallet_decrement($user->id, $currency_id, $request->amount, $wallet->wallet_type);
                user_wallet_increment($receiver->id, $currency_id, $finalamount, $wallet->wallet_type);

                $trans = new Transaction();
                $trans->trnx = $txnid;
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = $currency_id;
                $trans_wallet = get_wallet($user->id, $currency_id, $wallet->wallet_type);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->amount      = $request->amount;
                $trans->charge      = $finalCharge*$rate;
                $trans->type        = '-';
                $trans->remark      = 'send';
                $trans->details     = trans('Send Money');
                $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.($receiver->company_name ?? $receiver->name ).'", "description": "'.$request->description.'"}';
                $trans->save();

                $trans = new Transaction();
                $trans->trnx = $txnid;
                $trans->user_id     = $receiver->id;
                $trans->user_type   = 1;
                $trans->currency_id = $currency_id;
                $trans->amount      = $finalamount;
                $trans_wallet = get_wallet($receiver->id, $currency_id, $wallet->wallet_type);
                $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                $trans->charge      = 0;
                $trans->type        = '+';
                $trans->remark      = 'send';
                $trans->details     = trans('Send Money');
                $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.($receiver->company_name ?? $receiver->name ).'", "description": "'.$request->description.'"}';
                $trans->save();
                if ($wallet->currency->type == 2) {

                    if($wallet->currency->code == 'ETH') {
                        RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                        $towallet = Wallet::where('user_id', $receiver->id)->where('wallet_type', 8)->where('currency_id', $currency_id)->first();
                        $tx = '{"from": "'.$wallet->wallet_no.'", "to": "'.$towallet->wallet_no.'", "value": "0x'.dechex($finalamount*pow(10,18)).'"}';
                        RPC_ETH_Send('personal_sendTransaction',$tx, $wallet->keyword ?? '');
                    }
                    elseif($wallet->currency->code == 'BTC') {
                        $towallet = Wallet::where('user_id', $receiver->id)->where('wallet_type', 8)->where('currency_id', $currency_id)->first();
                        $res = RPC_BTC_Send('sendtoaddress',[$towallet->wallet_no, amount($finalamount, 2)],$wallet->keyword);
                        if (isset($res->error->message)){
                            return redirect()->back()->with(array('error' => __('Error: ') . $res->error->message));
                        }
                    }
                    else {
                        RPC_ETH('personal_unlockAccount',[$wallet->wallet_no, $wallet->keyword ?? '', 30]);
                        $towallet = Wallet::where('user_id', $receiver->id)->where('wallet_type', 8)->where('currency_id', $currency_id)->first();
                        $tokenContract = $wallet->currency->address;
                        $result = erc20_token_transfer($tokenContract, $wallet->wallet_no, $towallet->wallet_no, $finalamount, $wallet->keyword);
                        if (json_decode($result)->code == 1){
                            return redirect()->back()->with(array('error' => 'Ethereum client error: '.json_decode($result)->message));
                        }
                    }
                }
                $currency = Currency::findOrFail($currency_id);
                mailSend('send_money',['amount'=>$finalamount, 'curr' => $currency->code, 'trnx' => $txnid, 'from' => ($user->company_name ?? $user->name), 'to' => ($receiver->company_name ?? $receiver->name ), 'charge'=> 0, 'date_time'=> $trans->created_at ], $receiver);
                mailSend('send_money',['amount'=>$request->amount, 'curr' => $currency->code, 'trnx' => $txnid, 'from' => ($user->company_name ?? $user->name), 'to' => ($receiver->company_name ?? $receiver->name ), 'charge'=> $finalCharge*$rate, 'date_time'=> $trans->created_at ], $user);


                return redirect(route('admin-user-accounts',$user->id))->with('message', 'Send money successfully.');
            }else{
                return redirect()->back()->with('error','Sender not found!');
            }
        }

        public function external($user_id, $wallet_id)
        {
            $data['wallet'] = Wallet::findOrFail($wallet_id);
            $user = User::findOrFail($user_id);
            $data['data'] = $user;
            $data['bankaccounts'] = BankAccount::whereUserId($user_id)->where('currency_id', $data['wallet']->currency_id)->pluck('subbank_id');
            $data['bankpoolaccounts'] = BankPoolAccount::where('currency_id', $data['wallet']->currency_id)->pluck('bank_id');
            $data['banks'] = SubInsBank::where('status', 1)->whereIn('id', array_merge($data['bankaccounts']->toArray(), $data['bankpoolaccounts']->toArray()))->get();
            $data['beneficiaries'] = Beneficiary::where('user_id', $user_id)->get();
            $data['other_bank_limit'] = Generalsetting::first()->other_bank_limit;

            return view('admin.user.external',$data);
        }

        public function external_send(Request $request)
        {
            $wallet = Wallet::where('id',$request->wallet_id)->with('currency')->first();
            $user = User::find($wallet->user_id);
            $beneficiary = Beneficiary::find($request->beneficiary_id);

            $other_bank_limit = Generalsetting::first()->other_bank_limit;
            if ($request->amount >= $other_bank_limit) {
                $rules = ['document' => 'required|mimes:xls,xlsx,pdf,jpg,png'];
            } else {
                $rules = ['document' => 'mimes:xls,xlsx,pdf,jpg,png'];
            }
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return redirect()->back()->with('error',$validator->getMessageBag()->toArray()['document'][0]);
            }


            if($user->bank_plan_id === null){
                return redirect()->back()->with('error','This user has to buy a plan to withdraw.');
            }

            if(now()->gt($user->plan_end_date)){
                return redirect()->back()->with('error','Plan Date Expired.');
            }

            $bank_plan = BankPlan::whereId($user->bank_plan_id)->first();
            $dailySend = BalanceTransfer::whereUserId($user->id)->whereDate('created_at', '=', date('Y-m-d'))->whereStatus(1)->sum('amount');
            $monthlySend = BalanceTransfer::whereUserId($user->id)->whereMonth('created_at', '=', date('m'))->whereStatus(1)->sum('amount');

            $global_range = PlanDetail::where('plan_id', $user->bank_plan_id)->where('type', 'withdraw')->first();

            $dailyTransactions = BalanceTransfer::whereType('other')->whereUserId($user->id)->whereDate('created_at', now())->get();
            $monthlyTransactions = BalanceTransfer::whereType('other')->whereUserId($user->id)->whereMonth('created_at', now()->month())->get();
            $transaction_global_cost = 0;
            $currency =  Currency::findOrFail($wallet->currency_id);
            $rate = getRate($currency);
            $transaction_global_fee = check_global_transaction_fee($request->amount/$rate, $user, 'withdraw');

            if ($global_range) {
                if($transaction_global_fee)
                {
                    $transaction_global_cost = $transaction_global_fee->data->fixed_charge + ($request->amount/($rate * 100)) * $transaction_global_fee->data->percent_charge;
                }
                $finalAmount = $request->amount - $transaction_global_cost*$rate;

                if($global_range->min > $request->amount/$rate){
                    return redirect()->back()->with('error','Request Amount should be greater than this '.$global_range->min);
                }

                if($global_range->max < $request->amount/$rate){
                    return redirect()->back()->with('error','Request Amount should be less than this '.$global_range->max);
                }

                $balance = user_wallet_balance($user->id, $currency->id);

                if($balance<0 || $finalAmount > $balance){
                    return redirect()->back()->with('error','Insufficient Balance!');
                }

                if($global_range->daily_limit <= $finalAmount){
                    return redirect()->back()->with('error','Your daily limitation of transaction is over.');
                }

                if($global_range->daily_limit <= $dailyTransactions->sum('final_amount')){
                    return redirect()->back()->with('error','Your daily limitation of transaction is over.');
                }


                if($global_range->monthly_limit < $monthlyTransactions->sum('final_amount')){
                    return redirect()->back()->with('error','Your monthly limitation of transaction is over.');
                }

                $data = new BalanceTransfer();

                $txnid = Str::random(4).time();
                if ($file = $request->file('document'))
                {
                    $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
                    $file->move('assets/doc',$name);
                    $data->document = $name;
                }

                $data->user_id = $user->id;
                $data->transaction_no = $txnid;
                $data->currency_id = $wallet->currency_id;
                $data->subbank = $request->subbank;
                $data->iban = $beneficiary->account_iban;
                $data->swift_bic = $beneficiary->swift_bic;
                $data->beneficiary_id = $request->beneficiary_id;
                $data->type = 'other';
                $data->cost = $transaction_global_cost*$rate;
                $data->payment_type = $request->payment_type;
                $data->amount = $request->amount;
                $data->final_amount = $finalAmount;
                $data->description = $request->des;
                $data->status = 0;
                $data->save();

                return redirect()->back()->with('message','Money Send successfully.');

            }
        }

        public function between($user_id, $wallet_id)
        {
            $data['wallet'] = Wallet::findOrFail($wallet_id);
            $data['data'] = User::findOrFail($user_id);
            return view('admin.user.between',$data);
        }

        public function between_send(Request $request)
        {
            $wallet = Wallet::where('id',$request->wallet_id)->with('currency')->first();
            $user = User::find($wallet->user_id);

            if(!isset($request->amount)) {
                return back()->with('error','Please input amount');
            }
            if(!isset($request->wallet_type)) {
                return back()->with('error','Please select Wallet');
            }

            $gs = Generalsetting::first();
            $fromWallet = Wallet::findOrFail($request->wallet_id);

            $toWallet = Wallet::where('currency_id', $fromWallet->currency_id)->where('user_id',$user->id)->where('wallet_type',$request->wallet_type)->where('user_type',1)->first();
            $currency =  Currency::findOrFail($fromWallet->currency_id);
            if ($currency->type == 2) {
                $keyword = str_rand(6);
                $address = RPC_ETH('personal_newAccount',[$keyword]);
                if ($address == 'error') {
                    return back()->with('error','You can not create this wallet because there is some issue in crypto node.');
                }
            }
            else {
                $address = $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999);
                $keyword = '';
            }
            if(!$toWallet){
                $toWallet = Wallet::create([
                    'user_id'     => $user->id,
                    'user_type'   => 1,
                    'currency_id' => $fromWallet->currency_id,
                    'balance'     => 0,
                    'wallet_type' => $request->wallet_type,
                    'wallet_no' => $address,
                    'keyword' => $keyword
                ]);
                if($request->wallet_type == 2) {
                    $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                    if(!$chargefee){
                        $chargefee = Charge::where('slug', 'card-issuance')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                    }

                    $trans = new Transaction();
                    $trans->trnx = str_rand();
                    $trans->user_id     = $user->id;
                    $trans->user_type   = 1;
                    $trans->currency_id = defaultCurr();
                    $trans->amount      = 0;

                    $trans_wallet = get_wallet($user->id, defaultCurr(), 1);
                    $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                    $trans->charge      = $chargefee->data->fixed_charge;
                    $trans->type        = '-';
                    $trans->remark      = 'card-issuance';
                    $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
                    $trans->details     = trans('Card Issuance');
                    $trans->save();
                }
                else {
                    $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', $user->id)->first();
                    if(!$chargefee) {
                        $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->where('user_id', 0)->first();
                    }

                    $trans = new Transaction();
                    $trans->trnx = str_rand();
                    $trans->user_id     = $user->id;
                    $trans->user_type   = 1;
                    $trans->currency_id = defaultCurr();

                    $trans_wallet = get_wallet($user->id, defaultCurr(), 1);
                    $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;

                    $trans->amount      = 0;
                    $trans->charge      = $chargefee->data->fixed_charge;
                    $trans->type        = '-';
                    $trans->remark      = 'account-open';
                    $trans->details     = trans('Wallet Create');
                    $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
                    $trans->save();
                }
                $currency = Currency::findOrFail(defaultCurr());
                $wallet_type_list = array('1'=>'Current', '2'=>'Card', '3'=>'Deposit', '4'=>'Loan', '5'=>'Escrow', '6'=>'Supervisor', '7'=>'Merchant', '8'=>'Crypto', '10'=>'Manager');

                mailSend('wallet_create',['amount'=>$trans->charge, 'trnx'=> $trans->trnx,'curr' => $currency->code, 'type'=>$wallet_type_list[$request->wallet_type], 'date_time'=> dateFormat($trans->created_at)], $user);

                user_wallet_decrement($user->id, defaultCurr(), $chargefee->data->fixed_charge, 1);
                user_wallet_increment(0, defaultCurr(), $chargefee->data->fixed_charge, 9);
            }

            if($fromWallet->balance < $request->amount){
                return back()->with('error','Insufficient balance to your '.$fromWallet->currency->code.' wallet');
            }

            $fromWallet->balance -=  $request->amount;
            $fromWallet->update();

            $toWallet->balance += $request->amount;
            $toWallet->update();


            $trnx              = new Transaction();
            $trnx->trnx        = str_rand();
            $trnx->user_id     = $user->id;
            $trnx->user_type   = 1;
            $trnx->currency_id = $fromWallet->currency->id;
            $trnx->wallet_id   = $fromWallet->id;
            $trnx->amount      = $request->amount ;
            $trnx->charge      = 0;
            $trnx->remark      = 'payment_between_accounts';
            $trnx->type        = '-';
            $trnx->details     = trans('Transfer  '.$fromWallet->currency->code.'money other wallet');
            $trnx->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.($user->company_name ?? $user->name).'"}';
            $trnx->save();

            $toTrnx              = new Transaction();
            $toTrnx->trnx        = $trnx->trnx;
            $toTrnx->user_id     = $user->id;
            $toTrnx->user_type   = 1;
            $toTrnx->currency_id = $toWallet->currency->id;
            $toTrnx->wallet_id   = $toWallet->id;
            $toTrnx->amount      = $request->amount;
            $toTrnx->charge      = 0;
            $toTrnx->remark      = 'payment_between_accounts';
            $toTrnx->type          = '+';
            $toTrnx->details     = trans('Transfer  '.$fromWallet->currency->code.'money other wallet');
            $toTrnx->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.($user->company_name ?? $user->name).'"}';
            $toTrnx->save();

            mailSend('exchange_money',['from_curr'=>$fromWallet->currency->code,'to_curr'=>$toWallet->currency->code,'charge'=> amount($charge,$fromWallet->currency->type,3),'from_amount'=> amount($request->amount,$fromWallet->currency->type,3),'to_amount'=> amount($finalAmount,$toWallet->currency->type,3),'date_time'=> dateFormat($trnx->created_at)],auth()->user());

            return back()->with('message','Money exchanged successfully.');
        }

        public function profileBanks($id)
        {
            $data = User::findOrFail($id);
            $data['data'] = $data;
            return view('admin.user.profilebanks',$data);
        }

        public function profileBankAccount($id) {
            $data = User::findOrFail($id);
            $data['data'] = $data;
            $data['subbank'] = SubInsBank::wherestatus(1)->get();
            $data['currencylist'] = Currency::wherestatus(1)->where('type', 1)->get();
            $data['bankaccount'] = BankAccount::where('user_id', $id)->get();
            return view('admin.user.profilebankaccount',$data);
        }

        public function updateinfo(Request $request) {
            $bank_account = BankAccount::find($request->bank_account_id);
            $bank_account->iban = $request->iban;
            $bank_account->swift = $request->swift;
            $bank_account->save();
            return back()->with('message', 'Updated successfully');
        }

        public function storeBankAccount(Request $request) {

            $bankaccount = BankPoolAccount::where('bank_id', $request->subbank)->where('currency_id', $request->currency)->first();
            if ($bankaccount){
                return redirect()->back()->with(array('warning' => 'This bank account already exists.'));
            }

            $data = new BankPoolAccount();
            $data->bank_id = $request->subbank;
            $data->currency_id = $request->currency;
            $data->iban = $request->iban;
            $data->swift = $request->swift;

            $data->save();

            return redirect()->back()->with(array('message' => 'Bank Account has been created successfully'));
        }

        public function profilekycinfo($id) {
            $data['data'] = User::findOrFail($id);
            $data['kycforms'] = KycForm::where('status', 1)->get();
            $data['url'] = route('admin.kyc.details',$id);
            if($data['data']->kyc_status == 1){
                $status  = __('Approved');
               }elseif($data['data']->kyc_status == 2){
                $status  = __('Rejected');
               }else{
                $status =  __('Pending');
               }

               if($data['data']->kyc_status == 1){
                $status_sign  = 'success';
               }elseif($data['data']->kyc_status == 2){
                $status_sign  = 'danger';
               }else{
                $status_sign = 'warning';
               }
            $data['status'] = $status;
            $data['status_sign'] = $status_sign;
            return view('admin.user.profilekycinfo', $data);
        }

        public function kycdatatables($id)
        {
            $datas = User::where('id', $id)->get();

            return Datatables::of($datas)
                                ->addColumn('action', function(User $data) {
                                    $url = route('admin.kyc.details',$data->id);
                                    return '<div class="btn-group mb-1">
                                        <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        '.'Actions' .'
                                        </button>
                                        <div class="dropdown-menu" x-placement="bottom-start">
                                        <a href="' .$url. '"  class="dropdown-item">'.__("Details").'</a>
                                        </div>
                                    </div>';
                                })
                                ->editColumn('kyc_method', function(User $data) {
                                    return strtoupper($data->kyc_method);
                                })

                               ->addColumn('kyc', function(User $data) {
                                   if($data->kyc_status == 1){
                                    $status  = __('Approved');
                                   }elseif($data->kyc_status == 2){
                                    $status  = __('Rejected');
                                   }else{
                                    $status =  __('Pending');
                                   }

                                   if($data->kyc_status == 1){
                                    $status_sign  = 'success';
                                   }elseif($data->kyc_status == 2){
                                    $status_sign  = 'danger';
                                   }else{
                                    $status_sign = 'warning';
                                   }

                                    return '<div class="btn-group mb-1">
                                    <button type="button" class="btn btn-'.$status_sign.' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        '.$status .'
                                    </button>
                                    <div class="dropdown-menu" x-placement="bottom-start">
                                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.user.kyc',['id1' => $data->id, 'id2' => 1]).'">'.__("Approve").'</a>
                                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.user.kyc',['id1' => $data->id, 'id2' => 2 ]).'">'.__("Reject").'</a>
                                    </div>
                                    </div>';

                                })
                                ->rawColumns(['action','status','kyc'])
                                ->toJson();
        }

        public function additionkycdatatables($id)
        {
            $datas = KycRequest::where('user_id', $id)->get();

            return Datatables::of($datas)
                               ->addColumn('action', function(KycRequest $data) {
                                   if($data->status == 1){
                                    $status  = __('Approved');
                                   }elseif($data->status == 2){
                                    $status  = __('Rejected');
                                   }else{
                                    $status =  __('Pending');
                                   }

                                   if($data->status == 1){
                                    $status_sign  = 'success';
                                   }elseif($data->status == 2){
                                    $status_sign  = 'danger';
                                   }else{
                                    $status_sign = 'warning';
                                   }

                                    return '<div class="btn-group mb-1">
                                    <button type="button" class="btn btn-'.$status_sign.' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        '.$status .'
                                    </button>
                                    <div class="dropdown-menu" x-placement="bottom-start">
                                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal1" class="dropdown-item" data-href="'. route('admin.more.user.kyc',['id1' => $data->id, 'id2' => 1]).'">'.__("Approve").'</a>
                                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal1" class="dropdown-item" data-href="'. route('admin.more.user.kyc',['id1' => $data->id, 'id2' => 2 ]).'">'.__("Reject").'</a>
                                    </div>
                                    </div>';

                                })
                                ->addColumn('detail', function(KycRequest $data) {
                                    $url = route('admin.more.user.kyc.details',$data->id);
                                    return '<div class="btn-group mb-1">
                                        <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        '.'Actions' .'
                                        </button>
                                        <div class="dropdown-menu" x-placement="bottom-start">
                                        <a href="' .$url. '"  class="dropdown-item">'.__("Details").'</a>
                                        </div>
                                    </div>';
                                })
                                ->rawColumns(['action', 'detail'])
                                ->toJson();
        }

        public function KycForm($id)
        {
            $user=User::findOrFail($id);
            return view('admin.user.kyc_more_forms', compact('user'));
        }

        public function StoreKycForm(Request $request)
        {
            $data = new KycRequest();
            $input = $request->all();
            $input['kyc_info'] = json_encode(array_values($request->form_builder));
            $input['request_date'] = date('Y-m-d H:i:s');
            $data->fill($input)->save();

            $msg = __('New Data Added Successfully.').' '.'<a href="'.route("admin.user.kycinfo", $request->user_id).'">'.__('View Lists.').'</a>';
            return response()->json($msg);
        }

        public function gateway(Request $request) {
            $bankgateway = BankGateway::where('subbank_id', $request->id)->first();
            return $bankgateway;
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
                    'amount' => 'required',
                    'sender' => 'required',
                    'receiver' => 'required',
                ];

                $validator = Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
                }
                $trnx            = Transaction::findOrFail($id);

                $currency_id = $trnx->currency_id;
                $userBalance = user_wallet_balance($trnx->user_id,$currency_id);
                $amount = $request->input('amount');
                $charge = $request->input('charge');
                $newTotal = $amount - $charge;

                if($trnx->type == "-")
                {
                    $totalAmt = $trnx->amount - $trnx->charge;
                    $balance = $userBalance + $totalAmt;

                    if($amount > $balance)
                    {
                        return response()->json(array('errors' => ['Customer Balance not Available.']));
                    }

                    user_wallet_increment($trnx->user_id, $currency_id, $trnx->amount);
                    user_wallet_decrement(0, $currency_id, $trnx->charge, 9);

                    $trnx->currency_id = $currency_id;
                    $trnx->amount      = $amount;
                    $trnx->charge      = $charge;
                    $trnx->remark      = $request->input('remark');
                // $trnx->type        = '-';
                    $json_data         = json_decode($trnx->data);
                    $json_data->sender = $request->sender;
                    $json_data->receiver = $request->receiver;
                    $trnx->data        = json_encode($json_data);
                    $trnx->details     = $request->input('description');
                    $trnx->save();

                    user_wallet_decrement($trnx->user_id, $currency_id, $amount);
                    user_wallet_increment(0, $currency_id, $charge, 9);

                    return response()->json('Transacton Update Success');
                }

                if($trnx->type == "+")
                {
                    $totalAmt = $trnx->amount - $trnx->charge;
                    $balance = $userBalance - $totalAmt;
                    user_wallet_decrement($trnx->user_id, $currency_id, $totalAmt);
                    user_wallet_decrement(0, $currency_id, $trnx->charge, 9);

                    $trnx->currency_id = $currency_id;
                    $trnx->amount      = $amount;
                    $trnx->charge      = $charge;
                    $trnx->remark      = $request->input('remark');
                    $json_data         = json_decode($trnx->data);
                    $json_data->sender = $request->sender;
                    $json_data->receiver = $request->receiver;
                    $trnx->data        = json_encode($json_data);
                // $trnx->type        = '-';
                    $trnx->details     = $request->input('description');
                    $trnx->save();
                    user_wallet_increment($trnx->user_id, $currency_id, $newTotal);
                    user_wallet_increment(0, $currency_id, $charge, 9);

                    return response()->json('Transacton Update Success');
                }



                // $trnx->trnx        = str_rand();
                // $trnx->user_id     = $id;
                // $trnx->user_type   = 1;


            }else{
                return response()->json(array('errors' => ['Should be correct button click.']));
            }

        }

        public function transctionDelete($id)
        {
            $trnx            = Transaction::findOrFail($id);
            $currency_id     = $trnx->currency_id;

            if($trnx->type == "-")
            {
                $totalAmt = $trnx->amount + $trnx->charge;
                user_wallet_increment($trnx->user_id, $currency_id, $totalAmt);
                $trnx->delete();
                return redirect()->back()->with(array('message' => 'Transacton Delete Success'));
            }

            if($trnx->type == "+")
            {
                $totalAmt = $trnx->amount + $trnx->charge;
                user_wallet_decrement($trnx->user_id, $currency_id, $totalAmt);
                $trnx->delete();
                return redirect()->back()->with(array('message' => 'Transacton Delete Success'));
            }
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
            $user = User::findOrFail($user_id);
            $transaction = Transaction::whereUserId($user_id)->orderBy('created_at', 'desc')->get();
            $gs = Generalsetting::first();
            $image = public_path('assets/images/'.$gs->logo);
            $image_encode = base64_encode(file_get_contents($image));
            $currency = Currency::findOrFail(defaultCurr());

            $e_balance = amount(userBalance($user->id), $currency->type, 2);
            $data = [
                'trans' => $transaction,
                'user'  => $user,
                'image' => $image_encode,
                'e_bal' => $e_balance,
                'def_code' => $currency->code
            ];

            $pdf = PDF::loadView('frontend.myPDF', $data);
            return $pdf->download('transaction.pdf');

        }

        public function transactionExport($user_id)
        {
            return Excel::download( new AdminExportTransaction($user_id), 'transaction.xlsx');
        }

        public function trandatatables($id)
        {
            $datas = Transaction::where('user_id',$id)->orderBy('created_at','desc')->get();

            return Datatables::of($datas)
                            ->editColumn('amount', function(Transaction $data) {
                                $currency = Currency::whereId($data->currency_id)->first();
                                return $data->type.amount($data->amount,$currency->type,2).$currency->code;
                            })
                            ->editColumn('trnx', function(Transaction $data) {
                                $trnx = $data->trnx;
                                return $trnx;
                            })
                            ->editColumn('sender', function(Transaction $data) {
                                return ucwords(json_decode($data->data)->sender ?? "");
                            })
                            ->editColumn('receiver', function(Transaction $data) {
                                return ucwords(json_decode($data->data)->receiver ?? "");
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
                                return '-'.amount($data->charge,$currency->type,2).$currency->code;
                            })
                            ->addColumn('action', function (Transaction $data) {
                                return ' <a href="javascript:;"  data-href="" onclick="getDetails('.$data->id.')" class="detailsBtn" >
                                ' . __("Details") . '</a>';
                            })

                            ->rawColumns(['action'])
                            ->toJson();
        }

        public function profileLoginHistory($id)
        {
            $user = User::findOrFail($id);
            $data['data'] = $user;
            return view('admin.user.profileloginhistory',$data);
        }

        public function loginhistorydatatables($id)
        {
            $datas = LoginActivity::where('user_id',$id)->orderBy('created_at','desc')->get();

            return Datatables::of($datas)
                            ->editColumn('created_at', function(LoginActivity $data) {
                                return dateFormat($data->created_at,'d-M-Y h:i:s');
                            })
                            ->toJson();
        }

        public function profileLayer($id)
        {
            $user = User::findOrFail($id);
            $data['data'] = $user;
            return view('admin.user.profilelayer',$data);
        }

        public function layerdatatables($id)
        {
            $datas = ChLayer::where('user_id',$id)->orderBy('created_at','desc')->get();

            return Datatables::of($datas)
                            ->editColumn('created_at', function(ChLayer $data) {
                                return dateFormat($data->created_at,'d-M-Y h:i:s');
                            })
                            ->toJson();
        }

        public function walletTrandatatables($id)
        {
            $datas = Transaction::where('wallet_id',$id)->orderBy('created_at','desc')->get();

            return Datatables::of($datas)
                            ->editColumn('amount', function(Transaction $data) {
                                $currency = Currency::whereId($data->currency_id)->first();
                                return $data->type.amount($data->amount,$currency->type,2).$currency->code;
                            })
                            ->editColumn('trnx', function(Transaction $data) {
                                $trnx = $data->trnx;
                                return $trnx;
                            })
                            ->editColumn('sender', function(Transaction $data) {
                                return ucwords(json_decode($data->data)->sender ?? "");
                            })
                            ->editColumn('receiver', function(Transaction $data) {
                                return ucwords(json_decode($data->data)->receiver ?? "");
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
                                return '-'.amount($data->charge,$currency->type,2).$currency->code;
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
            $globals = Charge::where('plan_id', $user->bank_plan_id)->where('user_id', 0)->orderBy('name','desc')->get();
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
                                $customplan =  Charge::where('user_id',$id)->where('plan_id', $data->plan_id)->where('name', $data->name)->first();
                                if ($customplan){
                                    return $customplan->data->percent_charge;
                                }
                                else {
                                    return 0;
                                }
                            })
                            ->editColumn('fixed_customer', function(Charge $data) use($id) {
                                $customplan =  Charge::where('user_id',$id)->where('plan_id', $data->plan_id)->where('name', $data->name)->first();

                                if ($customplan){
                                    return $customplan->data->fixed_charge;
                                }
                                else {
                                    return 0;
                                }
                            })
                            ->addColumn('action', function (Charge $data) use($id) {
                                $customplan =  Charge::where('user_id',$id)->where('plan_id', $data->plan_id)->where('name', $data->name)->first();

                                if($customplan) {
                                    return '<button type="button" class="btn btn-primary btn-big btn-rounded " onclick="getDetails('.$customplan->id.')" aria-haspopup="true" aria-expanded="false">
                                    Edit
                                    </button>';
                                }
                                else {

                                        return '<button type="button" class="btn btn-primary btn-big btn-rounded " data-id="'.$data->id.'" onclick="createDetails(\''.$data->id.'\')" aria-haspopup="true" aria-expanded="false">
                                        Edit
                                        </button>';
                                }
                            })

                            ->rawColumns(['action'])
                            ->toJson();
        }

        public function profilePricingplanSupervisordatatables($id)
        {
            $user = User::findOrFail($id);
            $globals = Charge::where('plan_id', $user->bank_plan_id)->where('user_id', 0)->orderBy('name','desc')->get();
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
                                $customplan =  Charge::where('user_id',$id)->where('plan_id', 0)->where('name', $data->name)->first();
                                if ($customplan){
                                    return $customplan->data->percent_charge;
                                }
                                else {
                                    return 0;
                                }
                            })
                            ->editColumn('fixed_customer', function(Charge $data) use($id) {
                                $customplan =  Charge::where('user_id',$id)->where('plan_id', 0)->where('name', $data->name)->first();

                                if ($customplan){
                                    return $customplan->data->fixed_charge;
                                }
                                else {
                                    return 0;
                                }
                            })
                            ->addColumn('action', function (Charge $data) use($id) {
                                $customplan =  Charge::where('user_id',$id)->where('plan_id', 0)->where('name', $data->name)->first();

                                if($customplan) {
                                    return '<button type="button" class="btn btn-primary btn-big btn-rounded " onclick="getDetails('.$customplan->id.')" aria-haspopup="true" aria-expanded="false">
                                    Edit
                                    </button>';
                                }
                                else {

                                        return '<button type="button" class="btn btn-primary btn-big btn-rounded " data-id="'.$data->id.'" onclick="createDetails(\''.$data->id.'\')" aria-haspopup="true" aria-expanded="false">
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

        public function profileAccountFee($id) {
            $wallet = Wallet::findOrFail($id);
            $user = User::findOrFail($wallet->user_id);
            $manual = Charge::where('plan_id', $user->bank_plan_id)->where('user_id', 0)->where('slug', 'manual')->get();
            return view('admin.user.walletfee', compact('wallet', 'manual'));

        }

        public function calmanualfee(Request $request) {
            $wallet = Wallet::where('id',$request->wallet_id)->with('currency')->first();
            $userBalance = user_wallet_balance($wallet->user_id, $wallet->currency->id, $wallet->wallet_type);
            $manualfee = Charge::findOrFail($request->charge_id);
            $customfee = Charge::where('plan_id', $manualfee->plan_id)->where('user_id', $wallet->user_id)->where('name', $manualfee->name)->first();
            $gs = Generalsetting::first();
            if ($customfee) {
                $manualfee = $customfee;
            }
            if ($manualfee->data->fixed_charge > $userBalance) {
                return redirect()->back()->with(array('warning' => 'Customer Balance not Available.'));
            }
            user_wallet_decrement($wallet->user_id, $wallet->currency->id,$manualfee->data->fixed_charge,$wallet->wallet_type);
            user_wallet_increment(0, $wallet->currency->id,$manualfee->data->fixed_charge,9);

            $trans = new Transaction();
            $trans->trnx = str_rand();
            $trans->user_id     = $wallet->user_id;
            $trans->user_type   = 1;
            $trans->currency_id = $wallet->currency->id;
            $trans->amount      = 0;

            $trans->wallet_id   = $wallet->id;
            $trans->charge      = $manualfee->data->fixed_charge;
            $trans->type        = '-';
            $trans->remark      = 'manual_fee_'.str_replace(' ', '_', $manualfee->name);
            $trans->data        = '{"sender":"'.(User::findOrFail($wallet->user_id)->company_name ?? User::findOrFail($wallet->user_id)->name).'", "receiver":"'.$gs->disqus.'"}';
            $trans->details     = trans('manual_fee');
            $trans->save();
            return redirect()->back()->with(array('message' => 'Done Manual fee successfully'));

        }

        public function profilePricingplancreate($id, $charge_id) {
            $global = Charge::findOrFail($charge_id);
            $user = User::findOrFail($id);
            $plandetail = new Charge();
            $plandetail->name = $global->name;
            $plandetail->user_id = $id;
            $plandetail->plan_id = $user->bank_plan_id;
            $plandetail->data =  $global->data;
            $plandetail->slug = $global->slug;
            return view('admin.user.profilepricingplanedit',compact('plandetail'));
        }

        public function profilePricingplanSupervisorcreate($id, $charge_id) {
            $global = Charge::findOrFail($charge_id);
            $user = User::findOrFail($id);
            $plandetail = new Charge();
            $plandetail->name = $global->name;
            $plandetail->user_id = $id;
            $plandetail->plan_id = 0;
            $plandetail->data =  $global->data;
            $plandetail->slug = $global->slug;
            return view('admin.user.profilepricingplanedit',compact('plandetail'));
        }

        public function profilePricingplanglobalcreate($id) {
            $plandetail = new Charge();
            $plandetail->user_id = 0;
            $plandetail->plan_id = $id;
            $plandetail->data =  json_decode('{"percent_charge":"0","fixed_charge":"0","from":"0","till":"0"}');
            return view('admin.user.profilepricingplancreate',compact('plandetail'));
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
            $gs = Generalsetting::first();
            foreach($request->section as $key=>$section){
                if (!$user->sectionCheck($section)) {
                    $manualfee = Charge::where('user_id', $id )->where('plan_id', $user->bank_plan_id)->where('name', $section)->first();
                    if(!$manualfee) {
                        $manualfee = Charge::where('user_id', 0)->where('plan_id', $user->bank_plan_id)->where('name', $section)->first();
                    }
                    if($manualfee) {
                        $trans = new Transaction();
                        $trans->trnx = str_rand();
                        $trans->user_id     = $id;
                        $trans->user_type   = 1;
                        $trans->currency_id = defaultCurr();
                        $trans->amount      = 0;
                        $trans_wallet = get_wallet($id, defaultCurr(), 1);
                        $trans->wallet_id   = isset($trans_wallet) ? $trans_wallet->id : null;
                        $trans->charge      = $manualfee->data->fixed_charge;
                        $trans->type        = '-';
                        $trans->remark      = 'module';
                        $trans->details     = $section.trans(' Section Create');
                        $trans->data        = '{"sender":"'.($user->company_name ?? $user->name).'", "receiver":"'.$gs->disqus.'"}';
                        $trans->save();

                        user_wallet_decrement($id, defaultCurr(), $manualfee->data->fixed_charge, 1);
                        user_wallet_increment(0, defaultCurr(), $manualfee->data->fixed_charge, 9);
                    }
                }
            }
            if (!empty($request->section)) {
                $input['section'] = implode(" , ", $request->section);
                $modules = [];
                foreach($request->section as $key=>$section) {
                    if($user->moduleCheck($section)) {
                        array_push($modules, $section);
                    }
                }
                $input['modules'] = implode(" , ", $modules);
            } else {
                $input['section'] = '';
            }

            $user->update($input);

            $msg = __('Module Successfully Changed.');
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
            if ($request->firstname)
            {
                $data['name'] = trim($request->firstname)." ".trim($request->lastname);
            }
            if ($request->kyc_method == 'manual') {
                $user->manual_kyc = $request->manual_kyc;
            }
            if(isset($request->form_select)){
                $is_private = $request->form_select == 0;
                if($is_private) {
                    $subscrib = BankPlan::findOrFail($user->bank_plan_id);
                    $subscription = BankPlan::where('type', 'private')->where('keyword', $subscrib->keyword)->first();
                    $data['bank_plan_id'] = $subscription->id;
                }
                else {
                    $subscrib = BankPlan::findOrFail($user->bank_plan_id);
                    $subscription = BankPlan::where('type', 'corporate')->where('keyword', $subscrib->keyword)->first();
                    $data['bank_plan_id'] = $subscription->id;
                }

                $data['personal_code'] = $is_private ? $request->personal_code : null;
                $data['your_id'] = $is_private ? $request->your_id : null;
                $data['issued_authority'] = $is_private ? $request->issued_authority : null;
                $data['date_of_issue'] = $is_private ? $request->date_of_issue : null;
                $data['date_of_expire'] = $is_private ? $request->date_of_expire : null;
                $data['company_address'] = $is_private ? null : $request->company_address;
                $data['company_name'] = $is_private ? null : $request->company_name;
                $data['company_reg_no'] = $is_private ? null : $request->company_reg_no;
                $data['company_vat_no'] = $is_private ? null : $request->company_vat_no;
                $data['company_dob'] = $is_private ? null : $request->company_dob;
                $data['company_type'] = $is_private ? null : $request->company_type;
                $data['company_city'] = $is_private ? null : $request->company_city;
                $data['company_country'] = $is_private ? null : $request->company_country;
                $data['company_zipcode'] = $is_private ? null : $request->company_zipcode;
            }

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
