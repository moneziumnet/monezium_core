<?php

namespace App\Http\Controllers\Admin;

use Zip;
use App\Models\Blog;
use App\Models\User;
use App\Models\Admin;
use App\Models\Contact;
use App\Models\Deposit;
use App\Models\Currency;
use App\Models\Withdraw;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use InvalidArgumentException;
use App\Models\Generalsetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\RequestDomain;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:admin');
    }


    public function index()
    {
        if (Auth::guard('admin')->user()->IsSuper()) {
            $data['ainstitutions'] = Admin::orderBy('id', 'desc')->where('tenant_id', '!=', '')->get();
            $data['languages'] = Language::all();
            $data['adomains'] = RequestDomain::orderBy('id', 'desc')->where('is_approved', 1)->get();
        } else {

            $data['blogs'] = Blog::all();
            $data['deposits'] = Deposit::all();
            $data['depositAmount'] = Deposit::sum('amount');
            $data['withdrawAmount'] = Withdraw::sum('amount');
            $data['withdrawChargeAmount'] = Withdraw::sum('fee');
            $data['currency'] = Currency::whereIsDefault(1)->first();
            $data['transactions'] = Transaction::all();
            $data['acustomers'] = User::orderBy('id', 'desc')->whereIsBanned(0)->get();
            $data['users'] = User::orderBy('id', 'desc')->get();
            $data['bcustomers'] = User::orderBy('id', 'desc')->whereIsBanned(1)->get();
            $data['payouts'] = Withdraw::where('status', 'completed')->sum('amount');

            $data['activation_notify'] = "";
        }
        // if (file_exists(public_path().'/rooted.txt')){
        //     $rooted = file_get_contents(public_path().'/rooted.txt');
        //     if ($rooted < date('Y-m-d', strtotime("+10 days"))){
        //         $activation_notify = "<i class='icofont-warning-alt icofont-4x'></i><br>Please activate your system.<br> If you do not activate your system now, it will be inactive on ".$rooted."!!<br><a href='".url('/admin/activation')."' class='btn btn-success'>Activate Now</a>";
        //     }
        // }

        return view('admin.dashboard', $data);
    }
    public function passwordreset()
    {
        $data = Auth::guard('admin')->user();
        return view('admin.password', compact('data'));
    }

    public function changepass(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if ($request->cpass) {
            if (Hash::check($request->cpass, $admin->password)) {
                if ($request->newpass == $request->renewpass) {
                    $input['password'] = Hash::make($request->newpass);
                } else {
                    return response()->json(array('errors' => [0 => 'Confirm password does not match.']));
                }
            } else {
                return response()->json(array('errors' => [0 => 'Current password Does not match.']));
            }
        }
        $admin->update($input);
        $msg = 'Successfully change your password';
        return response()->json($msg);
    }

    public function profile()
    {
        $data = tenancy()->central(function ($tenant){
            return Admin::findOrFail($tenant->id);
        });
        // $data = Auth::guard('admin')->user();
        $modules = Generalsetting::first();
        return view('admin.profile', compact('data', 'modules'));
    }

    public function profileupdate(Request $request)
    {
        //--- Validation Section

        $rules =
            [
                'photo' => 'mimes:jpeg,jpg,png,svg',
                'email' => 'unique:admins,email,' . Auth::guard('admin')->user()->id
            ];


        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends
        $input = $request->all();

        $data = tenancy()->central(function ($tenant){
            return Admin::findOrFail($tenant->id);
        });
        // $data = Auth::guard('admin')->user();

        if ($file = $request->file('photo')) {
            $name = Str::random(8) . time() . '.' . $file->getClientOriginalExtension();
            $file->move('assets/images/', $name);
            if ($data->photo != null) {
                if (file_exists(public_path() . '/assets/images/' . $data->photo)) {
                    unlink(public_path() . '/assets/images/' . $data->photo);
                }
            }
            $input['photo'] = $name;
        }
        $input['slug'] = str_replace(" ", "-", $input['name']);

        $data->update($input);
        
        $data = Auth::guard('admin')->user();
        $data->update($input);

        $msg = 'Successfully updated your profile';
        return response()->json($msg);
    }

    public function generate_bkup()
    {
        $bkuplink = "";
        $chk = file_get_contents('backup.txt');
        if ($chk != "") {
            $bkuplink = url($chk);
        }
        return view('admin.movetoserver', compact('bkuplink', 'chk'));
    }


    public function clear_bkup()
    {
        $destination  = public_path() . '/install';
        $bkuplink = "";
        $chk = file_get_contents('backup.txt');
        if ($chk != "") {
            unlink(public_path($chk));
        }

        if (is_dir($destination)) {
            $this->deleteDir($destination);
        }
        $handle = fopen('backup.txt', 'w+');
        fwrite($handle, "");
        fclose($handle);

        return redirect()->back()->with('success', 'Backup file Deleted Successfully!');
    }


    public function activation()
    {
        $activation_data = "";
        if (file_exists(public_path() . '/project/license.txt')) {
            $license = file_get_contents(public_path() . '/project/license.txt');
            if ($license != "") {
                $activation_data = "<i style='color:darkgreen;' class='icofont-check-circled icofont-4x'></i><br><h3 style='color:darkgreen;'>Your System is Activated!</h3><br> Your License Key:  <b>" . $license . "</b>";
            }
        }
        return view('admin.activation', compact('activation_data'));
    }


    public function activation_submit(Request $request)
    {

        $purchase_code =  $request->pcode;
        $my_script =  'Genius Bank - All in One Digital Banking System';
        $my_domain = url('/');

        $varUrl = str_replace(' ', '%20', config('services.genius.ocean') . 'purchase112662activate.php?code=' . $purchase_code . '&domain=' . $my_domain . '&script=' . $my_script);

        if (ini_get('allow_url_fopen')) {
            $contents = file_get_contents($varUrl);
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $varUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $contents = curl_exec($ch);
            curl_close($ch);
        }

        $chk = json_decode($contents, true);

        if ($chk['status'] != "success") {

            $msg = $chk['message'];
            return response()->json($msg);
        } else {
            $this->setUp($chk['p2'], $chk['lData']);

            if (file_exists(public_path() . '/rooted.txt')) {
                unlink(public_path() . '/rooted.txt');
            }

            $fpbt = fopen(public_path() . '/project/license.txt', 'w');
            fwrite($fpbt, $purchase_code);
            fclose($fpbt);

            $msg = 'Congratulation!! Your System is successfully Activated.';
            return response()->json($msg);
        }
    }

    function setUp($mtFile, $goFileData)
    {
        $fpa = fopen(public_path() . $mtFile, 'w');
        fwrite($fpa, $goFileData);
        fclose($fpa);
    }



    public function movescript()
    {
        ini_set('max_execution_time', 3000);

        $destination  = public_path() . '/install';
        $chk = file_get_contents('backup.txt');
        if ($chk != "") {
            unlink(public_path($chk));
        }

        if (is_dir($destination)) {
            $this->deleteDir($destination);
        }
        $src = base_path() . '/vendor/update';
        $this->recurse_copy($src, $destination);
        $files = public_path();
        $bkupname = 'GeniusCart-By-GeniusOcean-' . date('Y-m-d') . '.zip';
        $zip = Zip::create($bkupname)->add($files, true);
        $zip->close();

        $handle = fopen('backup.txt', 'w+');
        fwrite($handle, $bkupname);
        fclose($handle);

        if (is_dir($destination)) {
            $this->deleteDir($destination);
        }
        return response()->json(['status' => 'success', 'backupfile' => url($bkupname), 'filename' => $bkupname], 200);
    }

    public function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public function deleteDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    public function moduleupdate(Request $request)
    {
            $input = $request->all();

            $data = tenancy()->central(function ($tenant){
                return Admin::findOrFail($tenant->id);
            });

            if (!empty($request->section)) {
                $input['section'] = implode(" , ", $request->section);
            } else {
                $input['section'] = '';
            }
            $data->section = $input['section'];
            $data->update();
            $msg = 'Data Updated Successfully.';

            return response()->json($msg);
    }

    public function profileupdatecontact(Request $request)
    {
        //--- Validation Section

        $rules = [
            'fullname'   => 'required',
            'contact'   => 'required',
            'your_email'   => 'required',
            'your_phone'   => 'required',
            'your_address'   => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends
        $input = $request->all();
        if ($request->input('contact_id') > 0) {
            $id = $request->input('contact_id');
            $contact = tenancy()->central(function ($tenant) use ($id) {
                return Contact::findOrFail($id);
            });

            $contact->full_name    =  $request->input('fullname');
            $contact->contact     =  $request->input('contact');

            // $contact->dob           = $request->input('dob') ? date('Y-m-d', strtotime($request->input('dob'))) : '';
            $contact->personal_code = $request->input('personal_code');
            $contact->c_email       = $request->input('your_email');
            $contact->c_phone       = $request->input('your_phone');
            $contact->c_address     = $request->input('your_address');
            $contact->c_city        = $request->input('c_city');
            $contact->c_zip_code    = $request->input('c_zipcode');
            $contact->c_country     = $request->input('c_country_id');
            $contact->id_number     = $request->input('your_id');
            $contact->issued_authority = $request->input('issued_authority');
            // $contact->date_of_issue         = $request->input('issue_date') != "" ? date('Y-m-d', strtotime($request->input('issue_date'))) : '';
            // $contact->date_of_expire        = $request->input('expire_date') != "" ? date('Y-m-d', strtotime($request->input('expire_date'))) : '';

            if ($contact->save()) {
                $msg = 'Successfully updated your contact information.';
                return response()->json($msg);
            } else {
                $msg = 'Successfully updated your contact information.';
                return response()->json($msg);
            }
        } else {
            $contact = tenancy()->central(function ($tenant) use($request) {
                $contact = new Contact();

                $contact->full_name     =  $request->input('fullname');
                $contact->contact       =  $request->input('contact');
                $contact->user_id       = $tenant->id;
                $contact->dob           = $request->input('dob')  != "" ? date('Y-m-d', strtotime($request->input('dob'))) : '';
                $contact->personal_code = $request->input('personal_code');
                $contact->c_email       = $request->input('your_email');
                $contact->c_phone       = $request->input('your_phone');
                $contact->c_address     = $request->input('your_address');
                $contact->c_city        = $request->input('c_city');
                $contact->c_zip_code    = $request->input('c_zipcode');
                $contact->c_country     = $request->input('c_country_id');
                $contact->id_number             = $request->input('your_id');
                $contact->issued_authority      = $request->input('issued_authority');
                $contact->date_of_issue         = $request->input('date_of_issue') != "" ? date('Y-m-d', strtotime($request->input('date_of_issue'))) : '';
                $contact->date_of_expire        = $request->input('date_of_expire') != "" ? date('Y-m-d', strtotime($request->input('date_of_expire'))) : '';
                $contact->save();
                return $contact;
            });
            $msg = 'Successfully updated your contact information.';
            return response()->json($msg);
        }
    }
}
