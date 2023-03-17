<?php

namespace App\Http\Controllers\API;

use Auth;
use Datatables;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BankPlan;
use App\Models\User;
use App\Models\Charge;
use App\Models\Manager;
use App\Models\InviteUser;
use App\Models\Wallet;
use App\Models\Generalsetting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SupervisorController extends Controller
{

    public function referred(){
        try {
            if(!(check_user_type(4)))
            {
                if (!(Manager::where('manager_id', auth()->id())->first())) {
                    # code...
                    return redirect()->route('user.dashboard');
                }

            }
            $data['referreds'] = User::where('referral_id',auth()->id())->orderBy('id','desc')->paginate(20);
            $data['user'] = Auth::user();
            $data['wallets'] = Wallet::where('user_id',auth()->id())->where('wallet_type',6)->with('currency')->get();
            $data['managers'] = Manager::where('supervisor_id',auth()->id())->orderBy('id','desc')->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function invite_send(Request $request)
    {
        try {
            if(!check_user_type(4))
            {
                if (!(Manager::where('manager_id', auth()->id())->first())) {
                    return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You are  not Supervisor.']);
                }
            }
            $rules = [
                'invite_email'  => 'required|email'
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
               return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }

            $user = Auth::user();

            $data['user']   = $user;
            $input = $request->all();

            $get = InviteUser::where('user_id', $user->id )->where('invited_to', $request->input('invite_email'))->get();
            $u = User::where('email', $request->input('invite_email'))->get();

            if($get->count()>0 || $u->count()>0)
            {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This user already invited or registered.']);
            }
            $inviteUser = new InviteUser();
            $inviteUser->user_id = $user->id;
            $inviteUser->invited_to = $request->input('invite_email');
            $inviteUser->invite_type = 'Email';
            $inviteUser->status = 'Not Send';
            $inviteUser->created_at = date('Y-m-d H:i:s');

            $gs = Generalsetting::first();
            $to = $request->invite_email;
            $subject = " Invite you";
            $msg = "Hello!\nYou has been invited from ".$user->name." in ".$gs->from_name." .\nPlease confirm current.\n".url('/')."?reff=".$user->affilate_code."\n Thank you.";
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            sendMail($to,$subject,$msg,$headers);
            $inviteUser->save();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Invite sent successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function datatables($id)
    {
        try {
            $user = User::findOrFail($id);
            $globals = Charge::where('plan_id', $user->bank_plan_id)->whereIn('slug', ['deposit', 'send', 'recieve', 'escrow', 'withdraw', 'exchange', 'payment_between_accounts'])->get();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => compact('globals')]);

        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }
    public function edit($id) {
        try {
            $plandetail = Charge::findOrFail($id);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => compact('plandetail')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function create($id, $charge_id) {
        try {
            $global = Charge::findOrFail($charge_id);
            $plandetail = new Charge();
            $plandetail->name = $global->name;
            $plandetail->user_id = $id;
            $plandetail->plan_id = 0;
            $plandetail->data = $global->data;
            $plandetail->slug = $global->slug;
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => compact('plandetail')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function updateCharge(Request $request,$id)
    {
        try {
            if($request->fixed_charge < $request->percent_charge){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Percent charge amount can not be greater than fixed charge amount.']);
            }
            if($request->from && $request->from <= $request->fixed_charge){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Fixed charge should be less than minimum amount.']);
            }
            $rules  = [];
            $charge =  Charge::findOrFail($id);
            $inputs = $request->except('_token');
            foreach($inputs as $key =>  $input){
                $rules[$key] = 'required|numeric|min:0';
            }
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
               return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }
            $charge->data = $inputs;
            $charge->update();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => $charge->name.' Plan Charge Updated']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function createCharge(Request $request)
    {
        try {
            $data = new Charge();
            $data->name = $request->name;
            $data->user_id = $request->user_id;
            $data->plan_id = $request->plan_id;
            $data->slug = $request->slug;
            $inputs = $request->except(array('_token','name','user_id', 'plan_id', 'slug' ));
            foreach($inputs as $key =>  $input){
                $rules[$key] = 'required|numeric|min:0';
            }
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
               return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }
            $data->data = $inputs;

            $data->save();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Customer Plan Create Successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }


    public function storemanager(Request $request)
    {
        try {
            $rules = ['email' => 'required'];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
               return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }

            $manager = User::where('email', $request->email)->first();
            if (!$manager) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'The user of this email('.$request->email.') is not registered. Please add other manager.']);
            }
            $premanager = Manager::where('manager_id', $manager->id)->first();

            if ($manager->id == $request->supervisor_id) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You can not add your email as manager.']);
            }

            if ($premanager) {
                return back()->with('error','You already add this user as manager.');
            }
            $data = new Manager();
            $data->supervisor_id = $request->supervisor_id;
            $data->manager_id = $manager->id;
            $data->save();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Manager has been added successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

    public function deletemanager($id) {
        try {
            $manager = Manager::where('id', $id)->first();
            if (!$manager) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This user is not manager account.']);
            }
            $manager->delete();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Manager has been deleted successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }


    }
}
