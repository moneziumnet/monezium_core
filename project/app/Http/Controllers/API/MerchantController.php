<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;
use App\Models\User;
use App\Models\UserApiCred;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Currency;
use App\Models\Escrow;
use App\Models\Charge;
use App\Models\MerchantSetting;
use App\Models\MerchantWallet;
use Illuminate\Support\Carbon;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Auth;
use Illuminate\Support\Facades\Hash;
use Image;

class MerchantController extends Controller
{
    public $successStatus = 200;
    /*********************START Merchant API******************************/

    public function index()
    {
        try {
            if(!check_user_type(3))
            {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You do not have merchant role.']);
            }
            $user = Auth::user();

            $cred = UserApiCred::where('user_id',$user->id)->first();
            if(!$cred){
                $userapicred = new UserApiCred();
                $userapicred->user_id = $user->id;
                $userapicred->access_key = (string) Str::uuid();
                $userapicred->mode = 0;

                $userapicred->save();
                $cred = UserApiCred::where('user_id',$user->id)->first();
            }

            $wallets = MerchantWallet::where('merchant_id', auth()->id())->with('currency')->with('shop')->get();

            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => compact('cred', 'user', 'wallets')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function setting($tab = "paypal") {
        try {
            $data['setting'] = MerchantSetting::where('user_id',auth()->id())
                ->where('keyword', $tab)
                ->first();
            $data['tab'] = $tab;
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function setting_update($tab = "paypal", Request $request) {
        try {
            $data = MerchantSetting::where('user_id', auth()->id())
                ->where('keyword', $tab)
                ->first();
            if (!$data) {
                $data = new MerchantSetting();
            }
            $data->user_id = auth()->id();
            if($tab == 'paypal') {
                $data->information = array(
                    'client_id' => $request->client_id,
                    'client_secret' => $request->client_secret,
                    'sandbox_check' => $request->sandbox_check == 'on'
                );
            } else {
                $data->information = array(
                    'key' => $request->key,
                    'secret' => $request->secret,
                );
            }
            $data->keyword = $tab;
            $data->save();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Merchant Setting has been updated successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    /*********************END Merchant API******************************/

}
