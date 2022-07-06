<?php

namespace App\Exports;

use Auth;
use App\Models\Transaction;
//use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportTransaction implements FromCollection
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
    public function collection()
    {
        $user = Auth::user();
        $transactions = Transaction::whereUserId(auth()->id())->orderBy('id','asc')->get(); 
        return $transactions;
    }
}
