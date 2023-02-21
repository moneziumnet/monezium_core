<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\InstallmentLog;
use App\Models\User;
use App\Models\UserLoan;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Datatables;
use Illuminate\Support\Carbon;

class LoanController extends Controller
{
    public function __construct()
    {

    }

    public function datatables(Request $request)
    {
        if($request->status == 'all'){
          $datas = UserLoan::orderBy('id','desc')->get();
        }else{
          $datas = UserLoan::where('status',$request->status)->orderBy('id','desc')->get();
        }

         return Datatables::of($datas)
                            ->editColumn('transaction_no', function(UserLoan $data) {
                              return '<div>
                                      '.$data->transaction_no.'
                                      <br>
                                      <span class="text-info">'.$data->plan->title.'</span>
                              </div>';
                            })

                            ->editColumn('user_id', function(UserLoan $data){
                              return '<div>
                                          <span>'.($data->user->company_name ?? $data->user->name).'</span>
                                          <p>'.$data->user->account_number.'</p>
                                      </div>';
                            })

                            ->editColumn('loan_amount', function(UserLoan $data){
                               return  '<div>
                                           '.$data->currency->symbol.amount($data->loan_amount,$data->currency->type, 2).'
                                           <br>
                                           <span class="text-info">Per Installment '.$data->currency->symbol.amount($data->per_installment_amount, $data->currency->type, 2).'</span>
                                       </div>';
                             })

                            ->editColumn('total_installment', function(UserLoan $data) {
                              return '<div>
                                      '.$data->total_installment.'
                                      <br>
                                      <span class="text-info">'.$data->given_installment.' Given</span>
                              </div>';
                            })

                            ->editColumn('total_amount', function(UserLoan $data) {
                              $curr = Currency::whereId($data->currency->id)->first();
                              return  '<div>
                                          '.$curr->symbol.round($data->total_installment * $data->per_installment_amount,2).'
                                          <br>
                                          <span class="text-info">Paid Amount '.$curr->symbol.amount($data->paid_amount, $data->currency->type, 2).'</span>
                                      </div>';
                            })

                            ->editColumn('next_installment', function(UserLoan $data){
                              return $data->next_installment ? $data->next_installment->toDateString() : '--';
                            })

                            ->addColumn('status', function(UserLoan $data) {

                              if($data->status==0){
                              $status= __('Pending');
                              }
                              elseif($data->status==1){
                                $status= __('Running');
                              }
                              elseif($data->status==3){
                                $status=__('Completed');
                              }
                              else{
                                $status=__('Rejected');
                              }

                              if($data->status==1){
                                $status_sign='success';
                              }
                              elseif($data->status==0){
                                $status_sign='warning';
                              }
                              elseif($data->status==3){
                                $status_sign='info';
                              }
                              else{
                                $status_sign='danger';
                              }

                              if($data->status==3){
                                return '<div class="btn-group mb-1">
                                    <span class="badge bg-'.$status_sign.'">'.$status .'</span>
                                </div>';
                              }else{
                                return '<div class="btn-group mb-1">
                                        <button type="button" class="btn btn-'.$status_sign.' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                          '.$status .'
                                        </button>
                                        <div class="dropdown-menu" x-placement="bottom-start">
                                          <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.loan.status',['id1' => $data->id, 'id2' => 0]).'">'.__("Pending").'</a>
                                          <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.loan.status',['id1' => $data->id, 'id2' => 1]).'">'.__("Approved").'</a>
                                          <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.loan.status',['id1' => $data->id, 'id2' => 2]).'">'.__("Rejected").'</a>
                                        </div>
                                      </div>';
                              }
                         })

                         ->addColumn('action', function(UserLoan $data) {

                          return '<div class="btn-group mb-1">
                          <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            '.'Actions' .'
                          </button>
                          <div class="dropdown-menu" x-placement="bottom-start">
                            <a href="' . route('admin.loan.log.show',$data->id) . '"  class="dropdown-item">'.__("Logs").'</a>
                            <a href="' . route('admin.loan.show',$data->id) . '"  class="dropdown-item">'.__("Details").'</a>
                          </div>
                        </div>';

                        })
                        ->rawColumns(['transaction_no','user_id','loan_amount','total_installment','total_amount','next_installment','status','action'])
                        ->toJson();
    }

    public function index(){
      $this->installmentCheck();
      return view('admin.loan.index');
    }

    public function running(){
      $this->installmentCheck();
      return view('admin.loan.running');
    }

    public function completed(){
      return view('admin.loan.completed');
    }

    public function pending(){
      return view('admin.loan.pending');
    }

    public function rejected(){
      return view('admin.loan.rejected');
    }

    public function status($id1,$id2){
      $data = UserLoan::findOrFail($id1);
      if($data->status == 1){
        $msg = 'Already Running this loan!';
        return response()->json($msg);
      }

      if($id2 == 1){
        if($user = User::where('id',$data->user_id)->first()){
          $currency = Currency::whereId($data->currency_id)->first()->id;
          $trans = new Transaction();
          $trans->trnx = $data->transaction_no;
          $trans->user_id     = $data->user_id;
          $trans->user_type   = 1;
          $trans->currency_id = $data->currency_id;
          $trans->amount      = $data->total_amount;
          $trans->charge      = 0;
          $trans->type        = '+';
          $trans->remark      = 'loan_create';
          $trans->details     = trans('loan requesting');
          $trans->data        = '{"sender":"'.$gs->disqus.'", "receiver":"'.($user->company_name ?? $user->name).'"}';
          $trans->save();
          user_wallet_increment($user->id, $currency, $data->loan_amount, 4);
        }
        $data->next_installment = Carbon::now()->addDays($data->plan->installment_interval);
      }
      $data->status = $id2;
      $data->update();

      $msg = 'Data Updated Successfully.';
      return response()->json($msg);
    }

    public function show($id){
      $data = UserLoan::findOrFail($id);
      $data['requiredInformations'] = json_decode($data->required_information,true);
      $data['data'] = $data;
      $data['currencyinfo'] = Currency::whereId($data->currency->id)->first();

      return view('admin.loan.show',$data);
    }

    public function logShow($id){
      $loan = UserLoan::findOrfail($id);
      $logs = InstallmentLog::where('transaction_no',$loan->transaction_no)->latest()->paginate(20);
      $currency = Currency::whereId($loan->currency->id)->first();

      return view('admin.loan.log',compact('loan','logs','currency'));
    }

    public function installmentCheck(){
      $loans = UserLoan::whereStatus(1)->get();
      $now = Carbon::now();

      foreach($loans as $key=>$data){
        if($data->given_installment == $data->total_installment){
          return false;
        }
        if($now->gt($data->next_installment)){
          $this->takeLoanAmount($data->user_id,$data->per_installment_amount, $data);
          $this->logCreate($data->transaction_no,$data->per_installment_amount,$data->user_id);

          $data->next_installment = Carbon::now()->addDays($data->plan->installment_interval);
          $data->given_installment += 1;
          $data->paid_amount += $data->per_installment_amount;
          $data->update();

          if($data->given_installment == $data->total_installment){
            $this->paid($data);
          }
        }
      }
    }

    public function takeLoanAmount($userId,$installment, $data){
      $user = User::whereId($userId)->first();
      $currency = $data->currency->id;
      $userBalance = user_wallet_balance($user->id, $currency, 4);
      if($user && $userBalance>=$installment){
        user_wallet_decrement($user->id, $currency, $installment, 4);
      }
    }

    public function paid($loan){
      $loan = UserLoan::whereId($loan->id)->first();
      if($loan){
          $loan->status = 3;
          $loan->next_installment = NULL;
          $loan->update();
      }
    }


    public function logCreate($transactionNo,$amount,$userId){
      $data = new InstallmentLog();
      $data->user_id = $userId;
      $data->transaction_no = $transactionNo;
      $data->type = 'loan';
      $data->amount = $amount;
      $data->save();
    }
}
