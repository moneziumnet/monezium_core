<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\AdminUserConversation;
use App\Models\AdminUserMessage;
use App\Models\Generalsetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Admin;
use Illuminate\Support\Facades\Validator;
use Datatables;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function datatables(Request $request)
    {
         $datas = AdminUserConversation::orderBy('id','desc')->get();
         //--- Integrating This Collection Into Datatables
         return Datatables::of($datas)
            ->setRowAttr([
                'style' => function(AdminUserConversation $data) {
                    if($data->status == 'open' ) {
                        return "background-color: #ffcaca;";
                    } else {
                        return "background-color: #ffffff;";
                    }
                },
            ])
            ->filter(function ($instance) use ($request) {

                if (!empty($request->get('name'))) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                        return Str::contains(Str::lower($row['name']), Str::lower($request->get('name'))) ? true : false;
                    });
                }
                if (!empty($request->get('department'))) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                        return Str::contains(Str::lower($row['department']), Str::lower($request->get('department'))) ? true : false;
                    });
                }
                if (!empty($request->get('priority'))) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                        return Str::contains(Str::lower($row['priority']), Str::lower($request->get('priority'))) ? true : false;
                    });
                }
                if (!empty($request->get('status'))) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                        return Str::contains(Str::lower($row['status']), Str::lower($request->get('status'))) ? true : false;
                    });
                }
            })

            ->editColumn('created_at', function(AdminUserConversation $data) {
                return dateFormat($data->created_at, 'Y-m-d H:i');
            })

            ->editColumn('priority', function(AdminUserConversation $data) {
                switch ($data->priority) {
                    case 'Low':
                        $pr_color = "text-success";
                        break;
                    case 'Medium':
                        $pr_color = 'text-warning';
                        break;
                    default:
                        $pr_color = "text-danger";
                        break;
                }
                return '<div class="'.$pr_color.'">
                        '.$data->priority.'.
                    </div>';
            })

            ->editColumn('message', function(AdminUserConversation $data) {
                return str_dis($data->message);
            })
            ->editColumn('status', function(AdminUserConversation $data) {
                return ucfirst($data->status);
            })

            ->addColumn('name', function(AdminUserConversation $data) {
                $name = $data->user->company_name ?? $data->user->name;
                return  $name;
            })
            ->addColumn('action', function(AdminUserConversation $data) {
                if ( $data->status == 'open') {
                    $close_button = '<a href="javascript:;" data-toggle="modal" data-target="#closeModal" class="dropdown-item" data-href="'.  route('admin.message.status',[$data->id, 'closed']).'">'.__("Close").'</a>';
                }
                else {
                    $close_button = '';
                }

            return '<div class="btn-group mb-1">
                <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    '.'Actions' .'
                </button>
                <div class="dropdown-menu" x-placement="bottom-start">
                    <a href="' . route('admin.message.show',$data->id) . '"  class="dropdown-item">'.__("Reply").'</a>'
                    .$close_button.
                '</div>
                </div>';
            })
            ->rawColumns(['name','created_at','message','status','priority','action'])
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function index()
    {
        return view('admin.message.index');
    }
    public function message($id)
    {
        if(!AdminUserConversation::where('id',$id)->exists())
        {
            return redirect()->route('admin.dashboard')->with('unsuccess',__('Sorry the page does not exist.'));
        }
        $conv = AdminUserConversation::findOrfail($id);

        $message_list = AdminUserMessage::where('conversation_id', $conv->id)->orderBy('id', 'desc')->get();
        $admin = Admin::where('id', 1)->first();
        return view('admin.message.create',compact('conv', 'admin', 'message_list'));
    }


    public function usercontact(Request $request)
    {
        $data = 1;
        $admin = Auth::guard('admin')->user();
        $user = User::where('email','=',$request->to)->first();
        if(empty($user))
        {
            $data = 0;
            return response()->json($data);
        }
        $to = $request->to;
        $subject = $request->subject;
        $from = $admin->email;
        $msg = nl2br($request->message);
        $gs = Generalsetting::findOrFail(1);

        $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
        sendMail($to,$subject,$msg,$headers);


        $conv = AdminUserConversation::where('user_id','=',$user->id)->where('subject','=',$subject)->first();


        if(isset($conv)){
            $msg = new AdminUserMessage();
            $msg->conversation_id = $conv->id;
            $msg->message = $request->message;
            $msg->save();
            return response()->json($data);
        }
        else{
            $message = new AdminUserConversation();
            $message->subject = $subject;
            $message->user_id= $user->id;
            $message->message=$request->message;
            $message->save();

            $msg = new AdminUserMessage();
            $msg->conversation_id = $message->id;
            $msg->message = $request->message;
            $msg->user_id=$user->id;
            $msg->save();

            return response()->json($data);
        }
    }
    public function postmessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error',$validator->getMessageBag()->toArray()['message'][0]);
        }

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
        $conv = AdminUserConversation::where('id', $request->conversation_id)->first();
        if (!$conv) {
            return response()->json(array('errors' => [0 => 'You can not reply this ticket, this ticket does not exist.']));
        }
        $conv->status = 'open';
        $conv->save();
        $input = $request->all();
        $msg->fill($input)->save();

        $gs = Generalsetting::findOrFail(1);
        $subject = $conv->subject;
        $from = $gs->from_email;
        $to = $conv->user->email;
        $msg = nl2br($request->message);

        $headers = "From: " . $gs->from_name . "<" . $from . ">";
        sendMail($to, $subject, $msg, $headers);

        //--- Redirect Section
        return redirect()->back()->with('message', 'You reply successfully.');
        // return response()->json('You reply successfully.');
        //--- Redirect Section Ends
    }

    public function ticket_status($id, $status)
    {
        $conv = AdminUserConversation::findOrfail($id);
        $conv->status = $status;
        $conv->save();
        return  response()->json('Ticket has been closed Successfully');
    }

    public function messageshow($id)
    {
        $conv = AdminUserConversation::findOrfail($id);

        $message_list = AdminUserMessage::where('conversation_id', $conv->id)->orderBy('id', 'desc')->get();
        $admin = Admin::where('id', 1)->first();
        return view('load.message',compact('conv', 'message_list', 'admin'));
    }
    public function messagedelete($id)
    {
        $conv = AdminUserConversation::findOrfail($id);

         AdminUserMessage::where('conversation_id',$conv->id)->delete();
          $conv->delete();
        //--- Redirect Section
        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }
}
