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
    public function datatables($id)
    {
        $datas = MerchantShop::where('merchant_id', $id)->orderBy('id','desc');
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
    public function index($id)
    {
        $data['data'] = User::findOrFail($id);
        return view('admin.merchantshop.index', $data);
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
        $currencies = Currency::wherestatus(1)->get();
        $gs = Generalsetting::first();
        foreach ($currencies as $key => $value) {
            $address = $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999);
            $keyword = '';
            if($value->type == 2) {
                if($value->code == 'ETH') {

                    $address = RPC_ETH('personal_newAccount',['123123']);
                    if ($address == 'error') {
                        break;
                    }
                    $keyword = '123123';
                }
                elseif ($value->code == 'BTC') {
                    $key = str_rand();
                    $address = RPC_BTC_Create('createwallet',[$key]);
                    if ($address == 'error') {
                        break;
                    }
                    $keyword = $key;
                }
                else {
                    $eth_currency = Currency::where('code', 'Eth')->first();
                    $eth_wallet = MerchantWallet::where('merchant_id', $data->merchant_id)->where('shop_id', $data->id)->where('currency_id', $eth_currency->id)->first();
                    if (!$eth_wallet) {
                        break;
                    }
                    $address = $eth_wallet->wallet_no;
                    $keyword = $eth_wallet->keyword;
                }
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
