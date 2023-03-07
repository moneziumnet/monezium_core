<?php

namespace App\Http\Controllers\User;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Auth;
use App\Models\AdminUserConversation;
use App\Models\AdminUserMessage;
use App\Models\Generalsetting;
use App\Models\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function adminmessages()
    {
        $user = Auth::guard('web')->user();
        $convs = AdminUserConversation::where('user_id', '=', $user->id)->orderBy('id', 'desc')->paginate(10);
        return view('user.message.index', compact('convs', 'user'));
    }

    public function messageload($id)
    {
        $conv = AdminUserConversation::findOrfail($id);
        $message_list = AdminUserMessage::where('conversation_id', $conv->id)->orderBy('id', 'desc')->get();
        $admin = Admin::where('id', 1)->first();

        return view('load.usermessage', compact('conv', 'admin', 'message_list'));
    }

    public function adminmessage($id)
    {
        $conv = AdminUserConversation::findOrfail($id);
        $message_list = AdminUserMessage::where('conversation_id', $conv->id)->orderBy('id', 'desc')->get();
        $admin = Admin::where('id', 1)->first();
        return view('user.message.create', compact('conv', 'admin', 'message_list'));
    }

    public function ticket_status($id, $status)
    {
        $conv = AdminUserConversation::findOrfail($id);
        $conv->status = $status;
        $conv->save();
        return redirect()->back()->with('message', 'Ticket has been closed Successfully');
    }


    public function adminmessagedelete($id)
    {
        $conv = AdminUserConversation::findOrfail($id);
        if ($conv->messages->count() > 0) {
            foreach ($conv->messages as $key) {
                $key->delete();
            }
        }
        $conv->delete();
        return redirect()->back()->with('message', 'Message Deleted Successfully');
    }

    public function adminpostmessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error',$validator->getMessageBag()->toArray()['message'][0]);
        }
        $conv = AdminUserConversation::where('user_id', '=', auth()->id())->where('id', $request->conversation_id)->first();

        if (!$conv) {
            return redirect()->back()->with('error', 'You can not reply this ticket because you are a owner of this ticket.');
        }
        $conv->status = 'open';
        $conv->save();

        $user = Auth::guard('web')->user();
        $gs = Generalsetting::findOrFail(1);
        $subject = $conv->subject;
        $to = $gs->from_email;
        $from = $user->email;
        $msg =  nl2br($request->message);

        $headers = "From: " . ($user->company_name ?? $user->name) . "<" . $from . ">";
        sendMail($to, $subject, $msg, $headers);

        $msg = new AdminUserMessage();
        $data = [];
        if($request->hasfile('document'))
        {
           foreach($request->file('document') as $file)
           {
               $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
               $file->move('assets/doc', $name);
               array_push($data, $name);
           }
        }
        $msg->document =  implode(",",$data);
        $input = $request->all();
        $msg->fill($input)->save();

        $notification = new Notification;
        $notification->conversation_id = $msg->conversation->id;
        $notification->save();
        //--- Redirect Section
        $msg = 'You reply successfully.';
        return redirect()->back()->with('message',$msg);
        //--- Redirect Section Ends
    }

    public function adminusercontact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error',$validator->getMessageBag()->toArray()['message'][0]);
        }
        $data = 1;
        $user = Auth::guard('web')->user();
        $gs = Generalsetting::findOrFail(1);
        $subject = $request->subject;
        $to = $gs->from_email;
        $from = $user->email;
        $msg = nl2br($request->message);

        $headers = "From: " . ($user->company_name ?? $user->name) . "<" . $from . ">";
        sendMail($to, $subject, $msg, $headers);

        $conv = AdminUserConversation::where('user_id', '=', $user->id)->where('subject', '=', $subject)->first();
        if (isset($conv)) {
            return redirect()->back()->with('error', 'This subject is already created.');
        } else {
            $message = new AdminUserConversation();
            $message->subject = $subject;
            $message->user_id = $user->id;
            $message->message = $request->message;
            $message->department = $request->department;
            $message->priority = $request->priority;
            $message->save();

            $notification = new Notification;
            $notification->conversation_id = $message->id;
            $notification->save();

            $data = [];
            if($request->hasfile('document'))
            {
               foreach($request->file('document') as $file)
               {
                   $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
                   $file->move('assets/doc', $name);
                   array_push($data, $name);
               }
            }

            $msg = new AdminUserMessage();
            $msg->conversation_id = $message->id;
            $msg->message = $request->message;
            $msg->user_id = $user->id;
            $msg->document =  implode(",",$data);
            $msg->save();
            return redirect()->back()->with('message', 'This subject have been created successfully.');
        }
    }
}
