<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;

use App\Models\Generalsetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    public function __construct()
    {
        $this->middleware('guest:admin', ['except' => ['logout']]);
    }


    public function showLoginForm()
    {
      return view('admin.login');
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

        // dd($current_domain);

        $user = Admin::where('email', $request->email)->first();

        if (!empty($user)) {
            if ($user->id == 1 && empty($user->tenant_id)) {
                if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {
                    return response()->json(route('admin.dashboard'));
                }
                else {
                    $msg = array(
                        'type' => 'warn',
                        'message' => "Credentials Doesn't Match !"
                    );
                    return response()->json(array('errors' => $msg));
                }
            }
            elseif (!empty($current_domain) || !empty($user->tenant_id))
            {
                if (empty($current_domain) && !empty($user->tenant_id))
                {
                    return response()->json(array('errors' => [ 0 =>  __('Permission denied, Please use your domain after approve') ]));
                }

                if(empty($user->tenant_id) && $user->status == 0) {
                    return response()->json(array('errors' => [ 0 =>  __('Permission denied, Please use your staff role after approve') ]));
                }

                $user = tenancy()->central(function ($tenant) {
                    return Admin::where('tenant_id', $tenant->id)->first();
                });
                if ($user->status == 1) {
                    if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {
                        return response()->json(route('admin.dashboard'));
                    }
                    else {
                        $msg = array(
                            'type' => 'warn',
                            'message' => "Credentials Doesn't Match !"
                        );
                        return response()->json(array('errors' => $msg));
                    }
                } else {
                    return response()->json(array('errors' => [ 0 => 'Please Contact to administrator' ]));
                }
            }
             else {
                return response()->json(array('errors' => [ 0 =>  __('permission denied') ]));
            }
        } else {
            return response()->json(array('errors' => [ 0 => 'admin not found' ]));
        }




        // if unsuccessful, then redirect back to the login with the form data

    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }

    public function showForgotForm()
    {
      return view('admin.forgot');
    }

    public function forgot(Request $request)
    {

      $gs = Generalsetting::findOrFail(1);
      $input =  $request->all();
      if (Admin::where('email', '=', $request->email)->count() > 0) {
      // user found
      $admin = Admin::where('email', '=', $request->email)->firstOrFail();
      $token = md5(time().$admin->name.$admin->email);

      $input['email_token'] = $token;
      $admin->update($input);

          mailSend('reset_password',['url'=>route('admin.change.token',$token)], $admin);

      return response()->json('Verification Link Sent Successfully!. Please Check your email.');
      }
      else{
      // user not found
      return response()->json(array('errors' => [ 0 => 'No Account Found With This Email.' ]));
      }
    }
}
