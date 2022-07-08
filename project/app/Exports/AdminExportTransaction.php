<?php

namespace App\Exports;

use Auth;
use App\Models\Transaction;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AdminExportTransaction implements FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    private $user_id;
    public function __construct($user_id)
    {
        $this->user_id = $user_id;
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
        $user = User::where('id', $this->user_id)->first();
        $transactions = Transaction::with('currency')->whereUserId($this->user_id)->orderBy('id','asc')->get(); 

        return view('admin.user.export.transaction',[
            'trans' => $transactions,
            'user'  => $user
        ]);
        
    }
}
