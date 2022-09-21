<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use App\Models\MerchantShop;
use App\Models\User;
use App\Models\Currency;
use App\Models\Generalsetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Validator;

class MerchantShopController extends Controller
{
   public function __construct()
    {
        $this->middleware('auth:admin');
    }

    //*** JSON Request
    public function datatables()
    {
        $datas = MerchantShop::orderBy('id','desc');
         return Datatables::of($datas)
                            ->editColumn('merchant_id', function(MerchantShop $data){
                                $user = User::findOrFail($data->merchant_id);
                                return $user->email;
                            })
                            ->editColumn('document', function(MerchantShop $data){

                                return '<a href ="'.asset('assets/doc/'.$data->document).'" attributes-list download > Download Document </a>';
                            })
                            ->editColumn('status', function(MerchantShop $data) {
                                $status      = $data->status == 1 ? _('Approved') : _('Pending');
                                $status_sign = $data->status == 1 ? 'success'   : 'danger';

                                return '<div class="btn-group mb-1">
                                <button type="button" class="btn btn-'.$status_sign.' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                  '.$status .'
                                </button>
                                <div class="dropdown-menu" x-placement="bottom-start">
                                  <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.merchant.shop.status',['id1' => $data->id, 'id2' => 0]).'">'.__("Pending").'</a>
                                  <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.merchant.shop.status',['id1' => $data->id, 'id2' => 1]).'">'.__("Approved").'</a>
                                </div>
                              </div>';
                            })
                            ->rawColumns(['merchant_id','document','status'])
                            ->toJson();
    }

    //*** GET Request
    public function index()
    {
        return view('admin.merchantshop.index');
    }


    //*** GET Request
    public function edit($id)
    {
        $data = Currency::findOrFail($id);
        return view('admin.currency.edit',compact('data'));
    }


    public function status($id1,$id2)
    {
        $data = MerchantShop::findOrFail($id1);

        if($data->status == 1){
            $msg = 'Merchant shop already approved';
            return response()->json($msg);
        }
        $currencies = $data['currencylist'] = Currency::wherestatus(1)->get();
        $gs = Generalsetting::first();
        foreach ($currencies as $key => $value) {
            if($value->type == 2) {
                $address = RPC_ETH('personal_newAccount',['123123']);
                if ($address == 'error') {
                    return response()->json(array('errors' => [0 => __('You can not create this shop because there is some issue in crypto node.')]));
                }
                $keyword = '123123';
            }
            else {
                $address = $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999);
                $keyword = '';
            }
            DB::table('merchant_wallets')->insert([
                'merchant_id' => $data->merchant_id,
                'currency_id' => $value->id,
                'shop_id' => $data->id,
                'wallet_no' => $address,
                'keyword' => $keyword,
            ]);
        }
        $data->update(['status' => $id2]);
        $msg = __('Data Updated Successfully.');
        return ($msg);
    }

}
