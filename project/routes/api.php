<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\UserLoanController;
use App\Http\Controllers\API\UserDpsController;
use App\Http\Controllers\API\UserFdrController;
use App\Http\Controllers\API\UserWithdrawController;
use App\Http\Controllers\API\UserWithdrawBankController;
use App\Http\Controllers\API\UserDepositController;
use App\Http\Controllers\API\UserDepositBankController;
use App\Http\Controllers\API\UserOtherBankController;
use App\Http\Controllers\API\BeneficiaryController;
use App\Http\Controllers\API\VoucherController;
use App\Http\Controllers\API\InvoiceController;
use App\Http\Controllers\API\EscrowController;
use App\Http\Controllers\API\ExchangeMoneyController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\SendController;
use App\Http\Controllers\API\MerchantController;


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
    Route::get('/user/packages', [UserController::class,'packages']);
    Route::post('/user/change-password', [UserController::class,'changepassword']);
    Route::post('/user/support-tickets', [UserController::class,'supportmessage']);

    Route::post('/user/loan', [UserLoanController::class,'loan_index']);
    Route::get('/user/loan-plan', [UserLoanController::class,'loanplan']);
    Route::post('/user/pending-loans', [UserLoanController::class,'pendingloan']);
    Route::post('/user/running-loans', [UserLoanController::class,'runningloan']);
    Route::post('/user/paid-loans', [UserLoanController::class,'paidloan']);
    Route::post('/user/rejected-loans', [UserLoanController::class,'rejectedloan']);
    Route::post('/user/loan-amount', [UserLoanController::class,'loanamount']);
    Route::post('/user/loan-request', [UserLoanController::class,'loanrequest']);
    Route::post('/user/loan-finish', [UserLoanController::class,'loanfinish']);
    Route::get('/user/loan-logs/{id}', [UserLoanController::class,'loanlog']);

    Route::post('/user/send-money', [SendController::class, 'sendmoney']);
    Route::post('/user/request-money', [SendController::class, 'requestmoney']);
    Route::post('/user/approve-request-money/{$id}', [SendController::class, 'approvemoney']);
    Route::post('/user/cancel-request-money/{$id}', [SendController::class, 'requestcancel']);
    Route::post('/user/create-request', [SendController::class, 'create']);
    Route::post('/user/receive', [SendController::class, 'receive']);

    // Route::post('register', 'API\UserController@register');
    Route::post('/user/dps', [UserDpsController::class,'dps_index']);
    Route::get('/user/dps-plan', [UserDpsController::class,'dpsplan']);
    Route::post('/user/running-dps', [UserDpsController::class,'runningdps']);
    Route::post('/user/matured-dps', [UserDpsController::class,'matureddps']);
    Route::get('/user/dps-details/{id}', [UserDpsController::class,'dpsdetails']);
   
    Route::post('/user/fdr', [UserFdrController::class,'fdr_index']);
    Route::get('/user/fdr-plan', [UserFdrController::class,'fdrplan']);
    Route::get('/user/fdr-details/{id}', [UserFdrController::class,'fdrdetails']);
    Route::post('/user/running-fdr', [UserFdrController::class,'runningfdr']);
    Route::post('/user/closed-fdr', [UserFdrController::class,'closedfdr']);
    Route::post('/user/apply-fdr', [UserFdrController::class,'applyfdr']);
    Route::post('/user/finish-fdr', [UserFdrController::class,'finishfdr']);

    Route::post('user/fetch-withdraw-list',[UserWithdrawController::class,'withdraw']);
    Route::post('user/withdraw-create',[UserWithdrawController::class,'withdrawcreate']);
    Route::post('user/withdraw-details',[UserWithdrawController::class,'withdrawdetails']);

    Route::post('user/withdrawbank',[UserWithdrawBankController::class,'withdrawbank']);

    Route::post('user/deposit',[UserDepositController::class,'deposit']);
    Route::post('user/deposit-details',[UserDepositController::class,'depositdetails']);

    Route::post('user/other-bank-transfer',[UserOtherBankController::class,'otherbanktransfer']);
    Route::get('user/other-bank',[UserOtherBankController::class,'otherbank']);
    Route::post('user/other-bank-send',[UserOtherBankController::class,'otherbanksend']);

    Route::post('user/depositsbank',[UserDepositBankController::class,'depositsbank']);
    Route::post('user/deposit-bank-create',[UserDepositBankController::class,'depositbankcreate']);
    Route::post('user/deposit-gateways',[UserDepositBankController::class,'depositgateways']);

    Route::post('user/beneficiaries',[BeneficiaryController::class,'beneficiaries']);
    Route::post('user/beneficiaries-details',[BeneficiaryController::class,'beneficiariesdetails']);
    Route::post('user/beneficiaries-create',[BeneficiaryController::class,'beneficiariescreate']);
        
    Route::post('/user/vouchers', [VoucherController::class,'vouchers']);
    Route::post('/user/create-voucher', [VoucherController::class,'createvoucher']);
    Route::post('/user/reedem-voucher', [VoucherController::class,'reedemvoucher']);
    Route::post('/user/reedemed-history', [VoucherController::class,'reedemedhistory']);
    
    Route::post('/user/invoices', [InvoiceController::class,'invoices']);
    Route::post('/user/create-invoice', [InvoiceController::class,'createinvoice']);
    Route::post('/user/invoice-view', [InvoiceController::class,'invoiceview']);
    Route::post('/user/invoice-url', [InvoiceController::class,'invoiceurl']);

    Route::post('/user/make-escrow', [EscrowController::class,'makeescrow']);
    Route::post('/user/my-escrow', [EscrowController::class,'myescrow']);
    Route::post('/user/fetch-escrow-pending', [EscrowController::class,'escrowpending']);

    Route::post('user/exchange-money-history',[ExchangeMoneyController::class,'exchangemoneyhistory']);
    Route::post('user/exchange-recents',[ExchangeMoneyController::class,'exchangerecents']);
    Route::post('user/exchange-money',[ExchangeMoneyController::class,'exchangemoney']);

    Route::post('user/transactions',[TransactionController::class,'transactions']);
    Route::post('user/transfer-logs',[TransactionController::class,'transferlogs']);

    Route::post('user/merchant-api-key',[MerchantController::class,'apikey']);

});
