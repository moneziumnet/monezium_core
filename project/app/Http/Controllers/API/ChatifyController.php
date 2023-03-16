<?php

namespace App\Http\Controllers\API;

use Session;
use Validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ChLayer;
use App\Models\ChLayerLogin;
use Auth;
use Illuminate\Support\Str;

class ChatifyController extends Controller
{

    /**
     * Authenticate the connection for pusher
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $layer = new ChLayer();
            $input = $request->all();
            $layer->fill($input)->save();
            return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You created new layer successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }
    }

    public function login(Request $request) {
        try {
            $layer = Chlayer::where('layer_id', $request->layer_id)->where('pincode', $request->pincode)->first();
            if(!$layer){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'This layer don\'t exist. Please input again correctly.']);
            }
            else {
                $data = new ChLayerLogin();
                $data->user_id = $request->user_id;
                $data->status = 1;
                $data->layer_id = $layer->id;
                $data->save();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You have been login successfully.']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }

    public function logout(Request $request) {
        try {
            $layer = ChLayerLogin::where('layer_id', $request->layerid)->where('user_id', auth()->id())->first();
            if(!$layer){
                return response()->json(['status' => '401', 'error_code' => '0', 'message' => 'You can not logout because you created this layer.']);
            }
            else {
                $layer->delete();
                return response()->json(['status' => '200', 'error_code' => '0', 'message' => 'You have been logout successfully.']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => '401', 'error_code' => '0', 'message' => $th->getMessage()]);
        }

    }
}

