<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\UserTelegram;
use App\Models\UserWhatsapp;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;

class UserPincodeController extends Controller
{

    public function index()
    {
        try {
            $data['telegram'] = UserTelegram::where('user_id',auth()->id())->first();
            $data['whatsapp'] = UserWhatsapp::where('user_id',auth()->id())->first();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function tele_generate(Request $request)
    {
        try {
            $user = auth()->user();
            $telegram = UserTelegram::where('user_id', $user->id)->first();
            if(!$telegram){
                $telegram = new UserTelegram();
            }
            $telegram->user_id = $user->id;
            $telegram->pincode = Str::random(8);
            $telegram->save();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'PinCode is generated successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function whs_generate(Request $request)
    {
        try {
            $user = auth()->user();
            $whatsapp = UserWhatsapp::where('user_id', $user->id)->first();
            if(!$whatsapp){
                $whatsapp = new UserWhatsapp();
            }
            $whatsapp->user_id = $user->id;
            $whatsapp->pincode = Str::random(8);
            $whatsapp->save();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'PinCode is generated successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

}
