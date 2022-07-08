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

class ExportTransaction implements FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

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
        $transactions = Transaction::with('currency')->whereUserId(auth()->id())->orderBy('id','asc')->get(); 

        return view('user.export.transaction',[
            'trans' => $transactions,
            'user'  => $user
        ]);
        
    }
}
