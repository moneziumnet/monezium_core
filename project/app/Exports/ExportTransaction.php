<?php

namespace App\Exports;

use Auth;
use App\Models\Transaction;
use App\Models\Currency;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportTransaction implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    private $search;
    private $remark;
    private $s_time;
    private $e_time;
    private $wallet_id;
    public function __construct($search, $remark, $s_time, $e_time, $wallet_id)
    {
        $this->search = $search;
        $this->wallet_id = $wallet_id;
        $this->remark = $remark;
        $this->s_time = $s_time ? $s_time : '';
        $this->e_time = $e_time;
    }
    // public function headings():array{
    //     return[
    //         'Created_at',
    //         'Txnid',
    //         'Remark',
    //         'Amount'
    //     ];
    // }
    // public function collection()
    // {
    //     $user = Auth::user();
    //     $transactions = Transaction::whereUserId(auth()->id())->orderBy('id','asc')->get();
    //     return $transactions;
    // }
    public function view():View
    {
        $user = Auth::user();
        $transactions = Transaction::with('currency')->whereUserId(auth()->id())
        // ->where('wallet_id', $this->wallet_id)
        ->when($this->wallet_id,function($q){
            return $q->where('wallet_id',$this->wallet_id);
        })
        ->when($this->remark,function($q){
            return $q->where('remark',$this->remark);
        })
        ->when($this->search,function($q){
            return $q->where('trnx','LIKE',"%{$this->search}%");
        })
        ->whereBetween('created_at', [$this->s_time, $this->e_time])
        ->orderBy('id','asc')->get();

        return view('user.export.transaction',[
            'trans' => $transactions,
            'user'  => $user
        ]);

    }
}
