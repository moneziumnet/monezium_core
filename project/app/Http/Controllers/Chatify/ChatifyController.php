<?php

namespace App\Http\Controllers\Chatify;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ChLayer;
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
}

