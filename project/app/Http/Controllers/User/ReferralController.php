<?php

namespace App\Http\Controllers\User;

use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReferralBonus;
use App\Models\InviteUser;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class ReferralController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function referred(){
        $data['referreds'] = User::where('referral_id',auth()->id())->orderBy('id','desc')->paginate(20);
        return view('user.referral.index',$data);
    }
    
    public function commissions(){
        $data['commissions'] = ReferralBonus::where('to_user_id',auth()->id())->orderBy('id','desc')->paginate(20);
        return view('user.referral.commission',$data);
    }

    public function invite_user()
    {
        $data['user'] = Auth::user();  
        return view('user.referral.invite-user', $data);
    }
    
    
    public function invite_send(Request $request)
    {

        $rules = [
            'invite_email'  => 'required|email'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with('unsuccess', 'The invite email address field is required.');
           // return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $user = Auth::user(); 

        $data['user']   = $user;
        $input = $request->all();

        $get = InviteUser::where('user_id', $user->id )->where('invited_to', $request->input('invite_email'))->get();
        $u = User::where('email', $request->input('invite_email'))->get();

        if($get->count()>0 || $u->count()>0)
        {
            return redirect()->back()->with('unsuccess', 'This user already invited or registered.');
        }
        //echo $gs->from_name;exit;
        $inviteUser = new InviteUser();
        $inviteUser->user_id = $user->id;
        $inviteUser->invited_to = $request->input('invite_email');
        $inviteUser->invite_type = 'Email';
        $inviteUser->status = 'Not Send';
        $inviteUser->created_at = date('Y-m-d H:i:s');

        if($inviteUser->save())
        {
            
            // if($gs->is_smtp == 1)
            // {
            //     $data = [
            //         'to' => $receiver->email,
            //         'type' => "request money",
            //         'cname' => $receiver->name,
            //         'oamount' => $finalAmount,
            //         'aname' => "",
            //         'aemail' => "",
            //         'wtitle' => "",
            //     ];

            //     $mailer = new GeniusMailer();
            //     $mailer->sendAutoMail($data);            
            // }
            // else
            // {
            //     $to = $receiver->email;
            //     $subject = " Money send successfully.";
            //     $msg = "Hello ".$receiver->name."!\nMoney send successfully.\nThank you.";
            //     $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            //     mail($to,$subject,$msg,$headers);            
            // }
            return redirect()->back()->with('success', 'Invite sent successfully.');
        }else{
            return redirect()->back()->with('unsuccess', 'Email not send this time. Please check email address');
        }
    }
}
