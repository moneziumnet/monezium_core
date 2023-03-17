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
}
