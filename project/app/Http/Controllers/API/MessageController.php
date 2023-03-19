<?php

namespace App\Http\Controllers\API;


use App\Models\Admin;
use App\Models\AdminUserConversation;
use App\Models\AdminUserMessage;
use App\Models\Generalsetting;
use App\Models\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Auth;

class MessageController extends Controller
{

    public function adminmessages()
    {
        try {
            $user = auth()->user();
            $convs = AdminUserConversation::where('user_id', '=', $user->id)->orderBy('id', 'desc')->paginate(10);
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => compact('convs', 'user')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function adminmessage($id)
    {
        try {
            $conv = AdminUserConversation::findOrfail($id);
            $message_list = AdminUserMessage::where('conversation_id', $conv->id)->orderBy('id', 'desc')->get();
            $admin = Admin::where('id', 1)->first();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'success', 'data' => compact('conv', 'admin', 'message_list')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function ticket_status($id, $status)
    {
        try {
            $conv = AdminUserConversation::findOrfail($id);
            $conv->status = $status;
            $conv->save();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'Ticket has been closed Successfully']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function adminpostmessage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
            }
            $conv = AdminUserConversation::where('user_id', '=', auth()->id())->where('id', $request->conversation_id)->first();

            if (!$conv) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You can not reply this ticket because you are a owner of this ticket.']);
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
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => $msg]);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function adminusercontact(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => $validator->getMessageBag()->toArray()]);
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
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This subject is already created.']);
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
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'This subject have been created successfully.']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }
}
