<?php

namespace App\Http\Controllers\User;

use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReferralBonus;
use App\Models\InviteUser;
use App\Models\User;
use App\Classes\GeniusMailer;
use Illuminate\Support\Facades\Validator;

class ReferralController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        
    }

    public function referred(){
        if(!check_user_type(3))
        {
            return redirect()->route('user.dashboard');
        }
        $data['referreds'] = User::where('referral_id',auth()->id())->orderBy('id','desc')->paginate(20);
        return view('user.referral.index',$data);
    }
    
    public function commissions(){
        if(!check_user_type(3))
        {
            return redirect()->route('user.dashboard');
        }
        $data['commissions'] = ReferralBonus::where('to_user_id',auth()->id())->orderBy('id','desc')->paginate(20);
        return view('user.referral.commission',$data);
    }

    public function invite_user()
    {
        if(!check_user_type(3))
        {
            return redirect()->route('user.dashboard');
        }
        $data['user'] = Auth::user();  
        return view('user.referral.invite-user', $data);
    }
    
    
    public function invite_send(Request $request)
    {
        if(!check_user_type(3))
        {
            return redirect()->route('user.dashboard');
        }
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


        
        $data = [
            'to' => $request->input('invite_email'),
            'type' => "invite email",
            'cname' => $user->name,
            'oamount' => '',
            'aname' => "",
            'aemail' =>  $user->email,
            'wtitle' => "",
        ];

        $mailer = new GeniusMailer();
        $mailer->sendAutoMail($data); 

        if($mailer->sendAutoMail($data))
        {
            $inviteUser->save();
            
            return redirect()->back()->with('success', 'Invite sent successfully.');
        }else{
            return redirect()->back()->with('unsuccess', 'Email not send this time. Please check email address');
        }
    }
}
