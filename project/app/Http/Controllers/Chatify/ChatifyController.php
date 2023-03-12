<?php

namespace App\Http\Controllers\Chatify;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ChLayer;
use App\Models\ChLayerLogin;
use Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ChatifyController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Authenticate the connection for pusher
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $layer = new ChLayer();

        $input = $request->all();

        $layer->fill($input)->save();
        return redirect()->back()->with('message','You created new layer successfully.');
    }

    public function login(Request $request) {
        $layer = Chlayer::where('layer_id', $request->layer_id)->where('pincode', $request->pincode)->first();
        if(!$layer){
            return redirect()->back()->with('warning', 'This layer don\'t exist. Please input again correctly.');
        }
        else {
            $data = new ChLayerLogin();
            $data->user_id = $request->user_id;
            $data->status = 1;
            $data->layer_id = $layer->id;
            $data->save();
            return redirect(route(config('chatify.routes.prefix'),['layer' => $layer->id]))->with('message', 'You have been login successfully.');
        }
    }

    public function logout(Request $request) {
        $layer = ChLayerLogin::where('layer_id', $request->layerid)->where('user_id', auth()->id())->first();
        if(!$layer){
            return redirect()->back()->with('warning', 'You can not logout because you created this layer.');
        }
        else {
            $layer->delete();
            return redirect(route(config('chatify.routes.prefix')))->with('message', 'You have been logout successfully.');
        }
    }
}

