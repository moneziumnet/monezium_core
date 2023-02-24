<?php

namespace App\Http\Controllers\Admin;

use Auth;
use Validator;
use App\Models\User;
use App\Models\DepositBank;
use App\Models\BalanceTransfer;
use App\Models\Wallet;
use App\Models\Currency;
use App\Models\Charge;
use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\Beneficiary;
use App\Models\WebhookRequest;
use App\Models\SubInsBank;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Datatables;


class ReportTransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        return view('admin.report.index');
    }

    public function datatables(){

        $balancetransfers = BalanceTransfer::whereType('other')->orderBy('id','desc')->with('user')->get();
        $deposits = DepositBank::orderby('id','desc')->with('user')->get();
        $compare_list = [];
        foreach ($balancetransfers as $key => $value) {
            $temp = array();
            $temp['user_id'] = $value->user_id;
            $temp['type'] = 'External Transfer';
            $temp['trnx_no'] = $value->transaction_no;
            $temp['sender_name'] = $value->user->company_name ?? $value->user->name;
            $beneficiary = Beneficiary::findOrFail($value->beneficiary_id);
            $temp['receiver_name'] = $beneficiary->name;
            $bank = SubInsBank::where('id', $value->subbank)->first();
            $temp['bank_name'] = $bank->name ?? null;
            $currency = Currency::findOrFail($value->currency_id);
            $temp['amount'] = amount($value->final_amount, $currency->type, 2);
            $temp['currency_code'] = $currency->code;
            if ($value->status == 0 || $value->status == 3) {
                $status = 'pending';
            }
            else if($value->status == 1) {
                $status = 'complete';
            }
            else{
                $status = 'reject';
            }
            $temp['status'] = $status;
            $transaction = Transaction::where('user_id',$value->user_id)->whereIn('remark', ['External_Payment', 'Deposit_create' ])->where('data', 'LIKE', '%'.$value->transaction_no.'%')->orWhere('trnx', $value->transaction_no)->first();

            $temp['tran_id'] = $transaction->id ?? null;
            $temp['date'] = $value->created_at;
            array_push($compare_list, $temp);

        }


        foreach ($deposits as $key => $value) {
            $temp = array();
            $temp['user_id'] = $value->user_id;
            $temp['type'] = 'Bank Deposit';
            $temp['trnx_no'] = $value->deposit_number;
            $send_info = WebhookRequest::where('transaction_id', 'LIKE', '%'.$value->deposit_number)->orWhere('reference', 'LIKE', '%'.$value->deposit_number)->with('currency')->first();
            $temp['sender_name'] = $send_info->sender_name ?? null;
            $temp['receiver_name'] = $value->user->company_name ?? $value->user->name;
            $bank = SubInsBank::where('id', $value->sub_bank_id)->first();
            $temp['bank_name'] = $bank->name ?? null;
            $currency = Currency::findOrFail($value->currency_id);
            $temp['amount'] = amount($value->amount, $currency->type, 2);
            $temp['currency_code'] = $currency->code;
            $temp['status'] = $value->status;
            $transaction = Transaction::where('user_id',$value->user_id)->whereIn('remark', ['External_Payment', 'Deposit_create' ])->where('data', 'LIKE', '%'.$value->deposit_number.'%')->orWhere('trnx', $value->deposit_number)->first();

            $temp['tran_id'] = $transaction->id ?? null;
            $temp['date'] = $value->created_at;
            array_push($compare_list, $temp);

        }
        usort($compare_list, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        $datas = json_decode(json_encode($compare_list, true));



        return Datatables::of($datas)
            ->setRowAttr([
                'style' => function( $data) {
                    $webhook_request = WebhookRequest::where('reference', 'LIKE', '%'.$data->trnx_no)->orwhere('transaction_id',$data->trnx_no)->first();
                    if($data->status == 'pending' && (!$webhook_request || $webhook_request->status == "processing")) {
                        return "background-color: #ffcaca;";
                    } else {
                        return "background-color: #ffffff;";
                    }
                },
            ])
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('sender'))) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                        return Str::contains($row['sender_name'], $request->get('sender')) ? true : false;
                    });
                }
            })
            ->editColumn('date',function( $data){
                return dateFormat($data->date,'d-M-Y');
            })
            ->editColumn('amount', function( $data) {
                return  $data->amount.$data->currency_code;
            })
            ->editColumn('status', function( $data) {
                if($data->status == 'pending') {
                    $status = '<span class="badge badge-warning">pending</span>';
                } elseif ($data->status == 'complete') {
                    $status = '<span class="badge badge-success">completed</span>';
                } else {
                    $status = '<span class="badge badge-danger">rejected</span>';
                }
                return $status;
            })
            ->editColumn('action', function( $data) {
                return '<a href="javascript:;" onclick=getDetails('.$data->trnx_no.') class="detailsBtn">' . __("Details") . '</a>';
            })
            ->rawColumns(['date','amount','status','action'])
            ->toJson();
    }

}
