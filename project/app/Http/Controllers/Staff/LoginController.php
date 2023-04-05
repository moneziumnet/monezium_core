<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Staff;

use App\Models\Generalsetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    public function __construct()
    {
        $this->middleware('guest:staff', ['except' => ['logout']]);
    }


    public function showLoginForm()
    {
      return view('staff.login');
    }

    public function login(Request $request)
    {
        //--- Validation Section
        $input = $request->all();
        $rules = [
                    'email'   => 'required|email',
                    'password' => 'required'
                ];
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $central_domain = config('tenancy.central_domains')[0];
        $current_domain = tenant('domains');

        if (!empty($current_domain)) {
            $current_domain = $current_domain->pluck('domain')->toArray()[0];
        }


        $user = Staff::where('email', $request->email)->first();

        if (!empty($user) && $user->status == 1) {
            if($user->is_banned == 1)
            {
            //   Auth::guard('staff')->logout();
              return response()->json(array('errors' => [ 0 => 'You are Banned From this system!' ]));
            }

            if($user->email_verified == 'No')
            {
            //   Auth::guard('staff')->logout();
              return response()->json(array('errors' => [ 0 => 'Your Email is not Verified!' ]));
            }
            if (!empty($current_domain))
            {

                if (Auth::guard('staff')->attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {
                    return response()->json(route('staff.dashboard'));
                }
                else {
                    $msg = array(
                        'type' => 'warn',
                        'message' => "Credentials Doesn't Match !"
                    );
                    return response()->json(array('errors' => $msg));
                }

            }
             else {
                return response()->json(array('errors' => [ 0 =>  __('permission denied') ]));
            }
        } else {
            return response()->json(array('errors' => [ 0 => 'This user\'s staff role is disable.' ]));
        }
    }

    public function logout()
    {
        Auth::guard('staff')->logout();
        return redirect()->route('staff.login');
    }

    public function showForgotForm()
    {
      return view('staff.forgot');
    }

    public function forgot(Request $request)
    {

      $gs = Generalsetting::findOrFail(1);
      $input =  $request->all();
      if (Staff::where('email', '=', $request->email)->count() > 0) {
      // user found
      $staff = Staff::where('email', '=', $request->email)->firstOrFail();
      $token = md5(time().$staff->name.$staff->email);

      $input['email_token'] = $token;
      $staff->update($input);

          mailSend('reset_password',['url'=>route('staff.change.token',$token)], $staff);

      return response()->json('Verification Link Sent Successfully!. Please Check your email.');
      }
      else{
      // user not found
      return response()->json(array('errors' => [ 0 => 'No Account Found With This Email.' ]));
      }
    }
}
