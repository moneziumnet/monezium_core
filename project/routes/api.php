<?php

use App\Http\Controllers\Chatify\Api\MessagesController;
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
use App\Http\Controllers\API\UserInvestmentController;


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

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('api')->group(function () {
        Route::post('/user/dashboard', [UserController::class, 'dashboard']);
        Route::get('/user/packages', [UserController::class, 'packages']);
        Route::post('/user/change-password', [UserController::class, 'changepassword']);
        Route::post('/user/wallet', [UserController::class, 'wallet_create']);
        Route::post('/user/crypto/wallet', [UserController::class, 'crypto_wallet_create']);
        Route::post('/user/bankaccount/gateway',[UserController::class,'gateway']);
        Route::post('/user/qr-code-scan',   [UserController::class,'scanQR']);
        Route::get('/user/transactions', [UserController::class,'transaction']);
        Route::get('/user/transaction/details/{id}', [UserController::class,'trxDetails']);
        Route::post('/user/transactions/details/mail', [UserController::class,'sendToMail']);
        Route::get('/user/profile', [UserController::class,'profile']);
        Route::post('/user/profile', [UserController::class,'profileupdate']);
        Route::post('/user/createTwoFactor', [UserController::class,'createTwoFactor']);
        Route::post('/user/username-by-email', [UserController::class,'username_by_email']);
        Route::post('/user/username-by-phone', [UserController::class,'username_by_phone']);
        Route::get('/user/userlist-by-phone', [UserController::class,'userlist_by_phone']);
        Route::get('/user/security', [UserController::class,'security']);
        Route::post('/user/security/store', [UserController::class,'securitystore']);
        Route::post('/user/module/update', [UserController::class,'moduleupdate']);
        Route::get('/user/aml_kyc', [UserController::class,'aml_kyc']);
        Route::post('/user/aml_kyc/update', [UserController::class,'aml_kyc_store']);
        Route::get('/user/aml_kyc/history', [UserController::class,'aml_kyc_history']);
        Route::get('/user/login/activity', [UserController::class,'history']);

        Route::get('/user/loan', [UserLoanController::class, 'loan_index']);
        Route::get('/user/loan-plan', [UserLoanController::class, 'loanplan']);
        Route::post('/user/loan-amount', [UserLoanController::class, 'loanamount']);
        Route::post('/user/loan-request', [UserLoanController::class, 'loanrequest']);
        Route::post('/user/loan-finish', [UserLoanController::class, 'loanfinish']);
        Route::get('/user/loan-logs/{id}', [UserLoanController::class, 'loanlog']);

        Route::get('/user/invest', [UserInvestmentController::class,'index']);
        Route::post('/user/dps-plan', [UserDpsController::class,'planDetails']);
        Route::post('/user/dps-submit', [UserDpsController::class,'dpsSubmit']);
        Route::post('/user/dps/finish', [UserDpsController::class,'finish']);
        Route::get('/user/dps-logs/{id}', [UserDpsController::class,'log']);
        Route::post('/user/fdr-amount', [UserFdrController::class,'fdrAmount']);
        Route::post('/user/fdr-request', [UserFdrController::class,'fdrRequest']);
        Route::post('/user/fdr/finish', [UserFdrController::class,'finish']);

        Route::get('/user/vouchers', [VoucherController::class, 'vouchers']);
        Route::get('/user/create-voucher',   [VoucherController::class,'create']);
        Route::post('/user/create-voucher',   [VoucherController::class,'submit']);
        Route::get('/user/reedem-voucher',  [VoucherController::class,'reedemForm']);
        Route::post('/user/reedem-voucher',  [VoucherController::class,'reedemSubmit']);
        Route::get('/user/reedemed-history',  [VoucherController::class,'reedemHistory']);

        Route::post('/user/send-money', [SendController::class, 'sendmoney']);
        Route::post('/user/request-money', [SendController::class, 'requestmoney']);
        Route::post('/user/approve-request-money/{$id}', [SendController::class, 'approvemoney']);
        Route::post('/user/cancel-request-money/{$id}', [SendController::class, 'requestcancel']);
        Route::post('/user/create-request', [SendController::class, 'create']);
        Route::post('/user/receive', [SendController::class, 'receive']);


        Route::post('user/fetch-withdraw-list', [UserWithdrawController::class, 'withdraw']);
        Route::post('user/withdraw-create', [UserWithdrawController::class, 'withdrawcreate']);
        Route::post('user/withdraw-details', [UserWithdrawController::class, 'withdrawdetails']);

        Route::post('user/withdrawbank', [UserWithdrawBankController::class, 'withdrawbank']);

        Route::post('user/deposit', [UserDepositController::class, 'deposit']);
        Route::post('user/deposit-details', [UserDepositController::class, 'depositdetails']);

        Route::post('user/other-bank-transfer', [UserOtherBankController::class, 'otherbanktransfer']);
        Route::get('user/other-bank', [UserOtherBankController::class, 'otherbank']);
        Route::post('user/other-bank-send', [UserOtherBankController::class, 'otherbanksend']);

        Route::post('user/depositsbank', [UserDepositBankController::class, 'depositsbank']);
        Route::post('user/deposit-bank-create', [UserDepositBankController::class, 'depositbankcreate']);
        Route::post('user/deposit-gateways', [UserDepositBankController::class, 'depositgateways']);

        Route::post('user/beneficiaries', [BeneficiaryController::class, 'beneficiaries']);
        Route::post('user/beneficiaries-details', [BeneficiaryController::class, 'beneficiariesdetails']);
        Route::post('user/beneficiaries-create', [BeneficiaryController::class, 'beneficiariescreate']);

        Route::post('/user/invoices', [InvoiceController::class, 'invoices']);
        Route::post('/user/create-invoice', [InvoiceController::class, 'createinvoice']);
        Route::post('/user/invoice-view', [InvoiceController::class, 'invoiceview']);
        Route::post('/user/invoice-url', [InvoiceController::class, 'invoiceurl']);

        Route::post('/user/make-escrow', [EscrowController::class, 'makeescrow']);
        Route::post('/user/my-escrow', [EscrowController::class, 'myescrow']);
        Route::post('/user/fetch-escrow-pending', [EscrowController::class, 'escrowpending']);

        Route::post('user/exchange-money-history', [ExchangeMoneyController::class, 'exchangemoneyhistory']);
        Route::post('user/exchange-recents', [ExchangeMoneyController::class, 'exchangerecents']);
        Route::post('user/exchange-money', [ExchangeMoneyController::class, 'exchangemoney']);

        Route::post('user/transactions', [TransactionController::class, 'transactions']);
        Route::post('user/transfer-logs', [TransactionController::class, 'transferlogs']);

        Route::post('user/merchant-api-key', [MerchantController::class, 'apikey']);

        // chatify
        Route::post('user/chat/auth', [MessagesController::class, 'pusherAuth'])->name('api.pusher.auth');
        Route::post('user/idInfo', [MessagesController::class, 'idFetchData'])->name('api.idInfo');
        Route::get('user/alluser', [MessagesController::class, 'allUserData'])->name('api.alluser');
        Route::post('user/sendMessage', [MessagesController::class, 'send'])->name('api.send.message');
        Route::post('user/fetchMessages', [MessagesController::class, 'fetch'])->name('api.fetch.messages');
        Route::get('user/download/{fileName}', [MessagesController::class, 'download'])->name('api.' . config('chatify.attachments.download_route_name'));
        Route::post('user/makeSeen', [MessagesController::class, 'seen'])->name('api.messages.seen');
        Route::get('user/getContacts', [MessagesController::class, 'getContacts'])->name('api.contacts.get');
        Route::post('user/star', [MessagesController::class, 'favorite'])->name('api.star');
        Route::post('user/favorites', [MessagesController::class, 'getFavorites'])->name('api.favorites');
        Route::get('user/search', [MessagesController::class, 'search'])->name('api.search');
        Route::post('user/shared', [MessagesController::class, 'sharedPhotos'])->name('api.shared');
        Route::post('user/deleteConversation', [MessagesController::class, 'deleteConversation'])->name('api.conversation.delete');
        Route::post('user/updateSettings', [MessagesController::class, 'updateSettings'])->name('api.avatar.update');
        Route::post('user/setActiveStatus', [MessagesController::class, 'setActiveStatus'])->name('api.activeStatus.set');
    });
});

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::prefix('api')->group(function () {
    Route::post('/user/login', [UserController::class, 'login']);
});
