<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::prefix('api')->group(function () {
    Route::post('/user/login', [UserController::class,'login']);
    Route::post('/user/register', [UserController::class,'register']);
    Route::post('/user/forgot', [UserController::class, 'forgot']);
    Route::post('/user/dashboard', [UserController::class,'dashboard']);

    Route::post('/user/loan', [UserController::class,'loan_index']);
    Route::get('/user/loan-plan', [UserController::class,'loanplan']);
    Route::post('/user/pending-loans', [UserController::class,'pendingloan']);
    Route::post('/user/running-loans', [UserController::class,'runningloan']);
    Route::post('/user/paid-loans', [UserController::class,'paidloan']);
    Route::post('/user/rejected-loans', [UserController::class,'rejectedloan']);
    Route::post('/user/loan-amount', [UserController::class,'loanamount']);
    Route::post('/user/loan-request', [UserController::class,'loanrequest']);
    Route::post('/user/loan-finish', [UserController::class,'loanfinish']);
    Route::get('/user/loan-logs/{id}', [UserController::class,'loanlog']);

    Route::post('/user/send-money', [UserController::class, 'sendmoney']);
    Route::post('/user/request-money', [UserController::class, 'requestmoney']);
    Route::post('/user/approve-requestmoney/{$id}', [UserController::class, 'approvemoney']);
    Route::post('/user/cancel-requestmoney/{$id}', [UserController::class, 'requestcancel']);
    Route::post('/user/create-request', [UserController::class, 'create']);
    Route::post('/user/receive', [UserController::class, 'receive']);

    // Route::post('register', 'API\UserController@register');
    Route::post('/user/dps', [UserController::class,'dps_index']);
    Route::get('/user/dps-plan', [UserController::class,'dpsplan']);
    Route::post('/user/running-dps', [UserController::class,'runningdps']);
    Route::post('/user/matured-dps', [UserController::class,'matureddps']);
    Route::get('/user/dps-details/{id}', [UserController::class,'dpsdetails']);
   
    Route::post('/user/fdr', [UserController::class,'fdr_index']);
    Route::get('/user/fdr-plan', [UserController::class,'fdrplan']);
    Route::get('/user/fdr-details/{id}', [UserController::class,'fdrdetails']);
    Route::post('/user/running-fdr', [UserController::class,'runningfdr']);
    Route::post('/user/closed-fdr', [UserController::class,'closedfdr']);
    Route::post('/user/apply-fdr', [UserController::class,'applyfdr']);
    Route::post('/user/finish-fdr', [UserController::class,'finishfdr']);
    
    Route::post('/user/make-escrow', [UserController::class,'makeescrow']);
    Route::post('/user/my-escrow', [UserController::class,'myescrow']);
    Route::post('/user/escrow-pending', [UserController::class,'escrowpending']);
    
    Route::post('/user/vouchers', [UserController::class,'vouchers']);
    Route::post('/user/create-voucher', [UserController::class,'createvoucher']);
    Route::post('/user/reedem-voucher', [UserController::class,'reedemvoucher']);
    Route::post('/user/reedemed-history', [UserController::class,'reedemedhistory']);
    
    Route::post('/user/invoices', [UserController::class,'invoices']);
    Route::post('/user/create-invoice', [UserController::class,'createinvoice']);
    Route::post('/user/invoice-view', [UserController::class,'invoiceview']);
    Route::post('/user/invoice-url', [UserController::class,'invoiceurl']);

    Route::post('user/exchange-money-history',[UserController::class,'exchangemoneyhistory']);
    Route::post('user/exchange-recents',[UserController::class,'exchangerecents']);
    Route::post('user/exchange-money',[UserController::class,'exchangemoney']);

    Route::post('user/transactions',[UserController::class,'transactions']);
    Route::post('user/transfer-logs',[UserController::class,'transferlogs']);
    Route::post('user/beneficiaries',[UserController::class,'beneficiaries']);
    Route::post('user/beneficiaries-details',[UserController::class,'beneficiariesdetails']);
    Route::post('user/beneficiaries-create',[UserController::class,'beneficiariescreate']);
    Route::post('user/other-bank-transfer',[UserController::class,'otherbanktransfer']);
    Route::post('user/deposit',[UserController::class,'deposit']);
    Route::post('user/deposit-details',[UserController::class,'depositdetails']);
    Route::post('user/depositsbank',[UserController::class,'depositsbank']);
    Route::post('user/withdrawbank',[UserController::class,'withdrawbank']);
    Route::post('user/withdraw-details',[UserController::class,'withdrawdetails']);
    Route::post('user/withdraw-create',[UserController::class,'withdrawcreate']);
    Route::post('user/deposit-gateways',[UserController::class,'depositgateways']);
    


});
