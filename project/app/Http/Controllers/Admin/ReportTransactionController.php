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
use Illuminate\Support\Carbon as Carbontime;
use GuzzleHttp\Client;
use Datatables;


class ReportTransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $banklist = SubInsBank::where('status', 1)->pluck('name');
        return view('admin.report.index', compact('banklist'));
    }

    public function datatables(Request $request){

        $balancetransfers = BalanceTransfer::whereType('other')->orderBy('id','desc')->with('user')->get();
        $deposits = DepositBank::orderby('id','desc')->with('user')->get();
        $compare_list = [];
        foreach ($balancetransfers as $key => $value) {
            $temp = array();
            $temp['user_id'] = $value->user_id;
            $temp['type'] = 'External';
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
            $temp['type'] = 'Deposit';
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
                    $webhook_request = WebhookRequest::where('reference', 'LIKE', '%'.$data->trnx_no.'%')->orWhere('transaction_id',$data->trnx_no)->first();
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
                        return Str::contains(Str::lower($row['sender_name']), Str::lower($request->get('sender'))) ? true : false;
                    });
                }
                if (!empty($request->get('receiver'))) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                        return Str::contains(Str::lower($row['receiver_name']), Str::lower($request->get('receiver'))) ? true : false;
                    });
                }
                if (!empty($request->get('trnx_no'))) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                        return Str::contains(Str::lower($row['trnx_no']), Str::lower($request->get('trnx_no'))) ? true : false;
                    });
                }
                if (!empty($request->get('trnx_type'))) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                        return Str::contains(Str::lower($row['type']), Str::lower($request->get('trnx_type'))) ? true : false;
                    });
                }
                if (!empty($request->get('bank_name'))) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                        return Str::contains(Str::lower($row['bank_name']), Str::lower($request->get('bank_name'))) ? true : false;
                    });
                }
                if (!empty($request->get('status'))) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                        return Str::contains(Str::lower($row['status']), Str::lower($request->get('status'))) ? true : false;
                    });
                }
                if (!empty($request->get('s_time'))) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                        if(dateFormat($row['date'], 'Y-m-d') >= dateFormat($request->get('s_time'), 'Y-m-d') && dateFormat($row['date'], 'Y-m-d') <= (dateFormat($request->get('e_time'), 'Y-m-d') ?? Carbontime::now()->addDays(1)->format('Y-m-d'))) {
                            return true;
                        }
                        else {
                            return false;
                        }
                    });
                }

                if (!empty($request->get('e_time'))) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                        if(dateFormat($row['date'], 'Y-m-d') <= dateFormat($request->get('e_time'), 'Y-m-d') ) {
                            return true;
                        }
                        else {
                            return false;
                        }
                    });
                }
            })
            ->editColumn('date',function( $data){
                return dateFormat($data->date,'m/d/Y');
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
                return '<div class="btn-group mb-1">
                    <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    '.'Actions' .'
                    </button>
                    <div class="dropdown-menu" x-placement="bottom-start">
                    <a  href="javascript:;" class="dropdown-item details" data-id="'.$data->tran_id.'" onclick = "getdetails(event)"  data-type="'.$data->type.'">'.__("Detail").'</a>
                    <a  href="javascript:;" class="dropdown-item details" data-id="'.$data->tran_id.'" onclick = "getfee(event)"  data-type="'.$data->type.'">'.__("Fee").'</a>
                    </div>
                </div>';
            })
            ->rawColumns(['date','amount','status','action'])
            ->toJson();
    }

    public function trxDetails($id)
    {
        $transaction = Transaction::where('id', $id)->with('currency')->first();
        if(!$transaction){
            return response('empty');
        }
        return view('admin.report.detail',compact('transaction'));
    }

    public function feeDetails($id)
    {
        $transaction = Transaction::where('id', $id)->with('currency')->first();
        if(!$transaction){
            return response('empty');
        }
        $webhook_request = WebhookRequest::where('transaction_id', $transaction->trnx)->orWhere('reference', 'LIKE', $transaction->trnx)->first();
        if(!$webhook_request){
            return response('empty');
        }
        return view('admin.report.feedetail',compact('transaction', 'webhook_request'));
    }

    public function totalfeeDetails(Request $request)
    {
        $s_time = $request->s_time ? $request->s_time : '';
        $e_time = $request->e_time ? $request->e_time : Carbontime::now()->addDays(1)->format('Y-m-d');
        $balancetransfers = BalanceTransfer::whereType('other')->orderBy('id','desc')->where('transaction_no', 'LIKE', '%'.$request->trnx_no.'%')->whereBetween('created_at', [$s_time, $e_time])->with('user')->get();
        $deposits = DepositBank::orderby('id','desc')->where('deposit_number', 'LIKE', '%'.$request->trnx_no.'%')->whereBetween('created_at', [$s_time, $e_time])->with('user')->get();
        $compare_list = [];
        if ($request->pay_type != 'Deposit') {
            foreach ($balancetransfers as $key => $value) {
                $temp = array();
                $temp['user_id'] = $value->user_id;
                $temp['type'] = 'External';
                $temp['trnx_no'] = $value->transaction_no;
                $temp['sender_name'] = $value->user->company_name ?? $value->user->name;
                if (!empty($request->sender_name)) {
                    if (!Str::contains(Str::lower($temp['sender_name']), Str::lower($request->sender_name))) {
                        continue;
                    }
                }
                $beneficiary = Beneficiary::findOrFail($value->beneficiary_id);
                $temp['receiver_name'] = $beneficiary->name;
                if (!empty($request->receiver_name)) {
                    if (!Str::contains(Str::lower($temp['receiver_name']), Str::lower($request->receiver_name))) {
                        continue;
                    }
                }
                $bank = SubInsBank::where('id', $value->subbank)->first();
                $temp['bank_name'] = $bank->name ?? null;
                if (!empty($request->bank_name)) {
                    if (!Str::contains(Str::lower($temp['bank_name']), Str::lower($request->bank_name))) {
                        continue;
                    }
                }
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
                if (!empty($request->pay_status)) {
                    if($status != $request->pay_status) {
                        continue;
                    }
                }
                $temp['status'] = $status;
                $transaction = Transaction::where('user_id',$value->user_id)->whereIn('remark', ['External_Payment', 'Deposit_create' ])->where('data', 'LIKE', '%'.$value->transaction_no.'%')->orWhere('trnx', $value->transaction_no)->first();

                $temp['tran_id'] = $transaction->id ?? null;
                $temp['date'] = $value->created_at;
                array_push($compare_list, $temp);

            }
        }
        if ($request->pay_type != 'External') {
            foreach ($deposits as $key => $value) {
                $temp = array();
                $temp['user_id'] = $value->user_id;
                $temp['type'] = 'Deposit';
                $temp['trnx_no'] = $value->deposit_number;
                $send_info = WebhookRequest::where('transaction_id', 'LIKE', '%'.$value->deposit_number)->orWhere('reference', 'LIKE', '%'.$value->deposit_number)->with('currency')->first();
                $temp['sender_name'] = $send_info->sender_name ?? null;
                if (!empty($request->sender_name)) {
                    if (!Str::contains(Str::lower($temp['sender_name']), Str::lower($request->sender_name))) {
                        continue;
                    }
                }
                $temp['receiver_name'] = $value->user->company_name ?? $value->user->name;
                if (!empty($request->receiver_name)) {
                    if (!Str::contains(Str::lower($temp['receiver_name']), Str::lower($request->receiver_name))) {
                        continue;
                    }
                }
                $bank = SubInsBank::where('id', $value->sub_bank_id)->first();
                $temp['bank_name'] = $bank->name ?? null;
                if (!empty($request->bank_name)) {
                    if (!Str::contains(Str::lower($temp['bank_name']), Str::lower($request->bank_name))) {
                        continue;
                    }
                }
                $currency = Currency::findOrFail($value->currency_id);
                $temp['amount'] = amount($value->amount, $currency->type, 2);
                $temp['currency_code'] = $currency->code;
                $temp['status'] = $value->status;
                if (!empty($request->pay_status)) {
                    if($temp['status'] != $request->pay_status) {
                        continue;
                    }
                }
                $transaction = Transaction::where('user_id',$value->user_id)->whereIn('remark', ['External_Payment', 'Deposit_create' ])->where('data', 'LIKE', '%'.$value->deposit_number.'%')->orWhere('trnx', $value->deposit_number)->first();

                $temp['tran_id'] = $transaction->id ?? null;
                $temp['date'] = $value->created_at;
                array_push($compare_list, $temp);

            }
        }
        usort($compare_list, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        $datas = json_decode(json_encode($compare_list, true));

        $currency_id = defaultCurr();
        $def_code = Currency::findOrFail($currency_id)->code;
        $client = new Client();
        $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency=' . $def_code);
        $rate = json_decode($response->getBody());

        $tran_fee = 0;
        $bank_fee = 0;
        foreach ($datas as $key => $data) {
            $transaction = Transaction::where('id', $data->tran_id)->with('currency')->first();
            if($transaction){
                $code = $transaction->currency->code;
                $tran_fee = $tran_fee + $transaction->charge / ($rate->data->rates->$code ?? $transaction->currency->rate);
            }
            else {
                continue;
            }
            $webhook_request = WebhookRequest::where('transaction_id', $transaction->trnx)->orWhere('reference', 'LIKE', $transaction->trnx)->first();
            if($webhook_request){
                $code = $transaction->currency->code;
                $bank_fee = $bank_fee + $webhook_request->charge / ($rate->data->rates->$code ?? $transaction->currency->rate);
            }
            else {
                continue;
            }
        }

        return view('admin.report.totalfee',compact('tran_fee', 'bank_fee' , 'def_code'));
    }

    public function summary_trxDetails($id)
    {
        $transaction = Transaction::where('id',$id)->with('currency')->first();
        if(!$transaction){
            return response('empty');
        }
        return view('admin.report.summary_detail',compact('transaction'));
    }

    public function summary() {
        $remark_list = Transaction::pluck('remark');
        return view('admin.report.summary', compact('remark_list'));
    }

    public function summary_fee(Request $request)
    {
        $search = request('search');
        $remark = request('remark');
        $s_time = request('s_time');
        $e_time = request('e_time');
        $s_time = $s_time ? $s_time : '';
        $e_time = $e_time ? $e_time : Carbontime::now()->addDays(1)->format('Y-m-d');
        $remark_list = Transaction::orderBy('remark', 'asc')->pluck('remark')->map(function ($item) {
            return ucfirst($item);
        });
        $remark_list = array_unique($remark_list->all());
        if($remark != 'all_mark' && $remark != null) {
            $transactions = Transaction::when($remark,function($q) use($remark){
                return $q->where('remark',$remark);
            })
            ->when($search,function($q) use($search){
                return $q->where('trnx','LIKE',"%{$search}%");
            })
            ->whereBetween('created_at', [$s_time, $e_time])
            ->with('currency')->latest()->paginate(20);
            $currency_id = defaultCurr();
            $def_code = Currency::findOrFail($currency_id)->code;
            $client = new Client();
            $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency=' . $def_code);
            $rate = json_decode($response->getBody());
            $balance = 0;

            foreach($transactions as $key => $value) {
                    $code = $value->currency->code;
                    if($value->type == '+') {
                        $balance = $balance - $value->charge / ($rate->data->rates->$code ?? $value->currency->rate);

                    }
                    else if ($value->type == '-') {
                        $balance = $balance - $value->charge / ($rate->data->rates->$code ?? $value->currency->rate);
                    }
            }
            $balance = amount($balance, Currency::findOrFail($currency_id)->type, 2).$def_code;
            $flag = false;

        }
        else {
            $transactions = array();
            $currency_id = defaultCurr();
            $def_code = Currency::findOrFail($currency_id)->code;
            $client = new Client();
            $response = $client->request('GET', 'https://api.coinbase.com/v2/exchange-rates?currency=' . $def_code);
            $rate = json_decode($response->getBody());
            $balance = 0;
            foreach ($remark_list as $key => $fee) {
                $fee_transactions = Transaction::when($fee,function($q) use($fee){
                    return $q->where('remark',$fee);
                })
                ->whereBetween('created_at', [$s_time, $e_time])
                ->with('currency')->latest()->paginate(20);
                $fee_balance = 0;
                foreach($fee_transactions as $key => $value) {
                    $code = $value->currency->code;
                    if($value->type == '+') {
                        $fee_balance = $fee_balance - $value->charge / ($rate->data->rates->$code ?? $value->currency->rate);

                    }
                    else if ($value->type == '-') {
                        $fee_balance = $fee_balance - $value->charge / ($rate->data->rates->$code ?? $value->currency->rate);

                    }

                }
                $balance = $balance + $fee_balance;
                $fee_balance = amount($fee_balance, Currency::findOrFail($currency_id)->type, 2).$def_code;
                array_push($transactions, array(
                    "fee"=> $fee,
                    "balance"=> $fee_balance
                ));

            }
            $currency_id = defaultCurr();
            $def_code = Currency::findOrFail($currency_id);

            $balance = amount($balance, $def_code->type, 2).$def_code->code;
            $flag = true;
        }

        return view('admin.report.summary',compact('transactions', 'search', 'remark_list', 's_time', 'e_time', 'balance' , 'flag'));
    }

}
