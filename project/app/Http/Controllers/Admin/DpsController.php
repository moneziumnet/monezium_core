<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\InstallmentLog;
use App\Models\User;
use App\Models\UserDps;
use Illuminate\Http\Request;
use Datatables;
use Illuminate\Support\Carbon;

class DpsController extends Controller
{
    public function __construct()
    {

    }

    public function datatables(Request $request)
    {
        if($request->status == 'all'){
          $datas = UserDps::orderBy('id','desc')->get();
        }else{
          $datas = UserDps::where('status',$request->status)->orderBy('id','desc')->get();
        }

         return Datatables::of($datas)

                            ->editColumn('transaction_no', function(UserDps $data) {
                                return '<div>
                                        '.$data->transaction_no.'
                                        <br>
                                        <span class="text-info">'.$data->plan->title.'</span>
                                </div>';
                            })
                            ->editColumn('deposit_amount', function(UserDps $data){
                                return  '<div>
                                            '.$data->currency->symbol.amount($data->deposit_amount, $data->currency->type, 2).'
                                            <br>
                                            <span class="text-info">Per Installment '.$data->currency->symbol.amount($data->per_installment, $data->currency->type, 2).'</span>
                                        </div>';
                            })
                            ->editColumn('user_id', function(UserDps $data){
                              return '<div>
                                          <span>'.$data->user->name.'</span>
                                          <p>'.$data->user->account_number.'</p>
                                      </div>';
                            })
                            ->editColumn('total_installment', function(UserDps $data) {
                                return '<div>
                                        '.$data->total_installment.'
                                        <br>
                                        <span class="text-info">'.$data->given_installment.' Given ('.$data->currency->symbol.amount($data->paid_amount, $data->currency->type, 2).')</span>
                                </div>';
                            })
                            ->editColumn('matured_amount', function(UserDps $data) {
                                return '<div>
                                        '.$data->currency->symbol.amount($data->matured_amount, $data->currency->type, 2).'
                                        <br>
                                        <span class="text-info">Interest Rate'.$data->interest_rate.' (%)</span>
                                </div>';
                            })
                            ->editColumn('next_installment', function(UserDps $data){
                                return $data->next_installment != NULL ? $data->next_installment->toDateString() : '--';
                            })
                            ->addColumn('action', function(UserDps $data) {

                              return '<div class="btn-group mb-1">
                              <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                '.'Actions' .'
                              </button>
                              <div class="dropdown-menu" x-placement="bottom-start">
                                <a href="' . route('admin.dps.log.show',$data->id) . '"  class="dropdown-item">'.__("Logs").'</a>
                              </div>
                            </div>';

                            })

                            ->rawColumns(['transaction_no','deposit_amount','user_id','total_installment','matured_amount','next_installment','action'])
                            ->toJson();
    }

    public function index(){
      $this->installmentCheck();
      return view('admin.dps.index');
    }

    public function running(){
      $this->installmentCheck();
      return view('admin.dps.running');
    }

    public function matured(){
      return view('admin.dps.matured ');
    }

    public function logShow($id){
      $dps = UserDps::findOrfail($id);
      $logs = InstallmentLog::where('transaction_no',$dps->transaction_no)->latest()->paginate(20);
      $currencyinfo = Currency::whereId($dps->currency->id)->first();

      return view('admin.dps.log',compact('dps','logs','currencyinfo'));
    }

    public function installmentCheck(){
        $dps = UserDps::whereStatus(1)->get();
        $now = Carbon::now();

        foreach($dps as $key=>$data){
          if($data->given_installment == $data->total_installment){
            if($data->is_given !=1){
              $this->maturedDps($data);
            }
            return false;
          }
          if($now->gt($data->next_installment)){
            $this->takeLoanAmount($data->user_id,$data->per_installment, $data);
            $this->logCreate($data->transaction_no,$data->per_installment,$data->user_id);

            $data->next_installment = Carbon::now()->addDays($data->plan->installment_interval);
            $data->given_installment += 1;
            $data->paid_amount += $data->per_installment;
            $data->update();

          }
        }
      }

    public function takeLoanAmount($userId,$installment, $data){
      $user = User::whereId($userId)->first();

      $currency = $data->currency->id;
      $userBalance = user_wallet_balance($user->id, $currency, 1);

      if($user && $userBalance>=$installment){
        user_wallet_decrement($user->id, $currency, $installment, 1);
        user_wallet_increment($user->id, $currency, $installment, 3);
      }
    }

    public function maturedDps($data){
      $dps = UserDps::whereId($data->id)->first();
      if($dps){
          $dps->status = 2;
          $dps->is_given = 1;
          $dps->next_installment = NULL;
          $dps->update();

          $this->sendMaturedMoney($dps->user_id,$dps->matured_amount, $dps);
      }
    }

    public function sendMaturedMoney($userId,$maturedAmount,$dps){
      $user = User::findOrfail($userId);
      $currency = $dps->currency->id;
      if($user){
        user_wallet_increment($user->id, $currency, $maturedAmount-$dps->deposit_amount, 3);
      }
    }

    public function logCreate($transactionNo,$amount,$userId){
      $data = new InstallmentLog();
      $data->user_id = $userId;
      $data->transaction_no = $transactionNo;
      $data->type = 'dps';
      $data->amount = $amount;
      $data->save();
    }
}
