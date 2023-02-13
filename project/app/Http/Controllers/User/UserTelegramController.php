<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\UserTelegram;
use App\Models\UserWhatsapp;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;

class UserTelegramController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['bot_login', 'bot_logout']]);
    }

    public function index()
    {
        $data['telegram'] = UserTelegram::where('user_id',auth()->id())->first();
        $data['whatsapp'] = UserWhatsapp::where('user_id',auth()->id())->first();
        return view('user.staff.pincode',$data);
    }
    public function generate(Request $request)
    {
        $user = auth()->user();
        $telegram = UserTelegram::where('user_id', $user->id)->first();
        if(!$telegram){
            $telegram = new UserTelegram();
        }
        $telegram->user_id = $user->id;
        $telegram->pincode = Str::random(8);
        $telegram->save();
        return redirect()->back()->with('message','PinCode is generated successfully.');
    }

    public function bot_login()
    {
        $user_email = request('email');
        $pincode = request('pincode');
        $user = User::where('email', $user_email)->first();
        if(!$user) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This user does not exist in our system.']);
        }
        $telegram = UserTelegram::where('user_id', $user->id)->where('pincode', $pincode)->first();
        if(!$telegram) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Pincode is not matched with email. Please input again']);
        }
        if($telegram->status == 1) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You are already login.']);
        }
        $chat_id = request('chat_id');
        $telegram->chat_id = $chat_id;
        $telegram->status = 1;
        $telegram->save();
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You have been login successfully']);
    }

    public function bot_logout()
    {
        $chat_id = request('chat_id');
        $telegram = UserTelegram::where('chat_id', $chat_id)->first();
        if(!$telegram) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'Pincode is not matched with any user']);
        }
        if($telegram->status == 0) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You are already logout.']);
        }
        $telegram->chat_id = $chat_id;
        $telegram->status = 0;
        $telegram->save();
        return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You have been logout successfully']);
    }

}
