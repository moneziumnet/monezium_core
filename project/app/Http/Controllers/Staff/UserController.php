<?php

namespace App\Http\Controllers\Staff;

use Datatables;
use App\Models\User;
use App\Models\Generalsetting;
use App\Models\Currency;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Auth;

class UserController extends Controller
{
    public function __construct()
        {
            $this->middleware('auth:staff');
        }

        public function datatables()
        {
             $datas = User::where('id', '!=', auth()->id())->orderBy('id','desc');

             return Datatables::of($datas)
                ->addColumn('name', function(User $data) {
                    $name = $data->company_name ?? $data->name;
                    return $name;
                })
                ->editColumn('balance', function(User $data) {
                    $currency = Currency::findOrFail(defaultCurr());
                    return '<div clase="text-right">'.$currency->symbol.amount(userBalance($data->id), $currency->type, 2).'</div>';
                })
                ->addColumn('action', function(User $data) {
                    return '<div class="btn-group mb-1">
                        <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        '.'Actions' .'
                        </button>
                        <div class="dropdown-menu" x-placement="bottom-start">
                        <a href="#"  class="dropdown-item">'.__("Profile").'</a>
                        </div>
                    </div>';
                })

                ->rawColumns(['name','action','balance'])
                ->toJson();
        }

        public function index()
        {
            return view('staff.user.index');
        }

}
