<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use App\Models\MerchantShop;
use App\Models\MerchantWallet;
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
        $datas = MerchantShop::where('merchant_id', $id)->orderBy('id','desc')->get();
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
                    $eth_wallet = MerchantWallet::where('merchant_id', $data->merchant_id)->where('shop_id', $data->id)->where('currency_id', $value->id)->first();
                    if (!$eth_wallet) {
                        $keyword = str_rand(6);
                        $address = RPC_ETH('personal_newAccount',[$keyword]);
                        if ($address == 'error') {
                            continue;
                        }
                    }
                    else {
                        continue;
                    }
                }
                elseif ($value->code == 'BTC') {
                    $key = str_rand();
                    $address = RPC_BTC_Create('createwallet',[$key]);
                    if ($address == 'error') {
                        continue;
                    }
                    $keyword = $key;
                }
                elseif ($value->code == 'TRON') {
                    $addressData = RPC_TRON_Create();
                    if ($addressData == 'error') {
                        continue;
                    }
                    $address = $addressData->address;
                    $keyword = $addressData->privateKey;
                }
                elseif($value->code == 'USDT(TRON)' && $value->curr_name == 'Tether USD TRC20') {
                    {
                        $tron_currency = Currency::where('code', 'TRON')->first();
                        $tron_wallet = MerchantWallet::where('merchant_id', $data->merchant_id)->where('shop_id', $data->id)->where('currency_id', $tron_currency->id)->first();
                        if (!$tron_wallet) {
                            
                            $addressData = RPC_TRON_Create();
                            if ($addressData == 'error') {
                                continue;
                            }
                            $address = $addressData->address;
                            $keyword = $addressData->privateKey;
                            DB::table('merchant_wallets')->insert([
                                'merchant_id' => $data->merchant_id,
                                'currency_id' => $tron_currency->id,
                                'shop_id' => $data->id,
                                'wallet_no' => $address,
                                'keyword' => $keyword,
                            ]);
                        } else {
                            $address = $tron_wallet->wallet_no;
                            $keyword = $tron_wallet->keyword;
                        }
                    }
                }
                else {
                    $eth_currency = Currency::where('code', 'ETH')->first();
                    $eth_wallet = MerchantWallet::where('merchant_id', $data->merchant_id)->where('shop_id', $data->id)->where('currency_id', $eth_currency->id)->first();
                    if (!$eth_wallet) {
                        $keyword = str_rand(6);
                        $address = RPC_ETH('personal_newAccount',[$keyword]);
                        if ($address == 'error') {
                            continue;
                        }
                        DB::table('merchant_wallets')->insert([
                            'merchant_id' => $data->merchant_id,
                            'currency_id' => $eth_currency->id,
                            'shop_id' => $data->id,
                            'wallet_no' => $address,
                            'keyword' => $keyword,
                        ]);
                    }
                    else {
                        $address = $eth_wallet->wallet_no;
                        $keyword = $eth_wallet->keyword;
                    }
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
        $user = User::findOrFail($data->merchant_id);
        mailSend('merchant_shop_approved',['shop_name'=>$data->name], $user);
        send_notification($user->id, 'Merchant shop for '.($user->company_name ?? $user->name).' is approved.'."\n Approved Merchant Shop Name : ".$data->name, route('admin.merchant.shop.index', $user->id));

        $msg = __('Data Updated Successfully.');
        return ($msg);
    }

}
