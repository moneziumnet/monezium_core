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
use App\Http\Controllers\API\BeneficiaryController;
use App\Http\Controllers\API\VoucherController;
use App\Http\Controllers\API\EscrowController;
use App\Http\Controllers\API\ExchangeMoneyController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\SendController;
use App\Http\Controllers\API\MerchantController;
use App\Http\Controllers\API\UserInvestmentController;
use App\Http\Controllers\API\ChatifyController;
use App\Http\Controllers\API\UserICOController;
use App\Http\Controllers\API\SupervisorController;
use App\Http\Controllers\API\MoneyRequestController;
use App\Http\Controllers\API\DepositBankController;
use App\Http\Controllers\API\OtherBankController;
use App\Http\Controllers\API\WithdrawCryptoController;
use App\Http\Controllers\API\OwnTransferController;
use App\Http\Controllers\API\ManageInvoiceController;
use App\Http\Controllers\API\UserContractManageController;
use App\Http\Controllers\API\MerchantMoneyRequestController;
use App\Http\Controllers\API\MerchantShopController;
use App\Http\Controllers\API\MerchantProductController;
use App\Http\Controllers\API\UserPincodeController;
use App\Http\Controllers\API\MessageController;

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
        Route::post('/user/register/{id}', [UserController::class,'register']);

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

        Route::get('/user/make-escrow',   [EscrowController::class,'create']);
        Route::post('/user/make-escrow',   [EscrowController::class,'store']);
        Route::get('/user/escrow/calcharge/{amount}',   [EscrowController::class,'calcharge']);
        Route::get('/user/my-escrow',   [EscrowController::class,'index']);
        Route::get('/user/escrow-pending',   [EscrowController::class,'pending']);
        Route::get('/user/escrow-dispute/{id}',   [EscrowController::class,'disputeForm']);
        Route::post('/user/escrow-dispute/{id}',   [EscrowController::class,'disputeStore']);
        Route::get('/user/release-escrow/{id}',   [EscrowController::class,'release']);

        Route::post('/user/layer/store', [ChatifyController::class,'store']);
        Route::post('/user/layer/login', [ChatifyController::class,'login']);
        Route::post('/user/layer/logout', [ChatifyController::class,'logout']);

        Route::get('/user/ico', [UserICOController::class, 'index']);
        Route::get('/user/ico/mytoken', [UserICOController::class, 'mytoken']);
        Route::get('/user/ico/edit/{id}', [UserICOController::class, 'edit']);
        Route::get('/user/ico/details/{id}', [UserICOController::class, 'details']);
        Route::get('/user/ico/buy/{id}', [UserICOController::class, 'show_buy']);
        Route::post('/user/ico/buy/{id}', [UserICOController::class, 'buy']);
        Route::get('/user/ico/delete/{id}', [UserICOController::class, 'delete']);
        Route::post('/user/ico/store', [UserICOController::class, 'store']);
        Route::post('/user/ico/update/{id}', [UserICOController::class, 'update']);

        Route::get('/user/supervisor',[SupervisorController::class,'referred']);
        Route::post('/user/invite-user',[SupervisorController::class,'invite_send']);
        Route::get('/user/pricingplan/edit/{id}', [SupervisorController::class, 'edit']);
        Route::get('/user/pricingplan/create/{id}/{charge_id}', [SupervisorController::class, 'create']);
        Route::get('/user/pricingplan/datatables/{id}', [SupervisorController::class, 'datatables']);
        Route::post('/user/pricingplan/updatecharge/{id}', [SupervisorController::class, 'updateCharge']);
        Route::post('/user/pricingplan/createcharge', [SupervisorController::class, 'createCharge']);
        Route::post('/user/manager/create', [SupervisorController::class, 'storemanager']);
        Route::get('/user/manager/delete/{id}', [SupervisorController::class, 'deletemanager']);

        Route::get('/user/send-money',[SendController::class,'create']);
        Route::post('/user/send-money',[SendController::class,'store']);

        Route::get('/user/money-request', [MoneyRequestController::class,'index']);
        Route::get('/user/money-request/create', [MoneyRequestController::class,'create']);
        Route::post('/user/money-request/store', [MoneyRequestController::class,'store']);
        Route::post('/user/request/money/send/{id}', [MoneyRequestController::class,'send']);
        Route::post('/user/request/money/cancel/{id}', [MoneyRequestController::class,'cancel']);
        Route::get('/user/request/money/delete/{id}', [MoneyRequestController::class,'delete']);
        Route::get('/user/money-request/details/{id}', [MoneyRequestController::class,'details']);

        Route::get('/user/bank/deposits',[DepositBankController::class,'index']);
        Route::get('/user/bank/deposit/create',[DepositBankController::class,'create']);
        Route::post('/user/bank/deposit/store',[DepositBankController::class,'store']);
        Route::get('/user/bank/deposit/bankcurrency/{id}',[DepositBankController::class,'bankcurrency']);

        Route::get('/user/other-bank/{id}',[OtherBankController::class,'othersend']);
        Route::post('/user/other-bank/store', [OtherBankController::class,'store']);
        Route::get('/user/other-bank/details/{id}', [OtherBankController::class, 'details']);

        Route::get('/user/crypto/withdraws',[WithdrawCryptoController::class,'index']);
        Route::get('/user/crypto/withdraw/create',[WithdrawCryptoController::class,'create']);
        Route::post('/user/crypto/withdraw/store',[WithdrawCryptoController::class,'store']);

        Route::get('/user/own', [OwnTransferController::class, 'index']);
        Route::post('/user/own',[OwnTransferController::class, 'transfer']);

        Route::get('/user/beneficiaries', [BeneficiaryController::class, 'index']);
        Route::post('/user/beneficiaries/store', [BeneficiaryController::class,'store']);
        Route::get('/user/beneficiaries/show/{id}', [BeneficiaryController::class,'show']);
        Route::post('/user/beneficiaries/update/{id}', [BeneficiaryController::class,'update']);

        Route::get('/user/exchange-money',   [ExchangeMoneyController::class,'exchangeForm']);
        Route::post('/user/exchange-money',  [ExchangeMoneyController::class,'submitExchange']);
        Route::get('/user/exchange-money/history',  [ExchangeMoneyController::class,'exchangeHistory']);
        Route::post('/user/exchange-money/calcharge',  [ExchangeMoneyController::class,'calcharge']);

        Route::get('/user/create-invoice',   [ManageInvoiceController::class,'create']);
        Route::get('/user/invoice/setting',   [ManageInvoiceController::class,'invoic_setting']);
        Route::post('/user/create-invoice',   [ManageInvoiceController::class,'store']);
        Route::post('/user/invoice/setting',   [ManageInvoiceController::class,'invoice_setting_save']);
        Route::get('/user/invoices',   [ManageInvoiceController::class,'index']);
        Route::post('/user/invoice/pay-status',   [ManageInvoiceController::class,'payStatus']);
        Route::post('/user/invoice/publish-status',   [ManageInvoiceController::class,'publishStatus']);
        Route::get('/user/invoices-edit/{id}',   [ManageInvoiceController::class,'edit']);
        Route::post('/user/invoices-update/{id}',   [ManageInvoiceController::class,'update']);
        Route::get('/user/invoice-cancel/{id}',   [ManageInvoiceController::class,'cancel']);
        Route::post('/user/invoice/send-mail',   [ManageInvoiceController::class,'sendToMail']);
        Route::get('/user/invoice/view/{number}',   [ManageInvoiceController::class,'view']);
        Route::get('/user/invoices-payment/{number}',   [ManageInvoiceController::class,'invoicePayment']);
        Route::post('/user/invoices-payment/submit',   [ManageInvoiceController::class,'invoicePaymentSubmit']);
        Route::get('/user/invoices-payment/submit/crypto/{id}',   [ManageInvoiceController::class,'invoicePaymentCrypto']);
        Route::get('/user/invoices/incoming',   [ManageInvoiceController::class,'incoming_index']);
        Route::get('/user/invoices/incoming/edit/{id}',   [ManageInvoiceController::class,'incoming_edit']);
        Route::post('/user/invoices/incoming/update/{id}',   [ManageInvoiceController::class,'incoming_update']);
        Route::post('/user/invoice/tax/create',   [ManageInvoiceController::class,'tax_create']);

        Route::get('/user/contract',   [UserContractManageController::class,'index']);
        Route::get('/user/contract/view/{id}',   [UserContractManageController::class,'view']);
        Route::get('/user/contract/create',   [UserContractManageController::class,'create']);
        Route::post('/user/contract/store',   [UserContractManageController::class,'store']);
        Route::post('/user/contract/sign/{id}',   [UserContractManageController::class,'contract_sign']);
        Route::get('/user/contract/edit/{id}',   [UserContractManageController::class,'edit']);
        Route::post('/user/contract/update/{id}',   [UserContractManageController::class,'update']);
        Route::get('/user/contract/delete/{id}',   [UserContractManageController::class,'delete']);
        Route::post('/user/contract/send-mail',   [UserContractManageController::class,'sendToMail']);
        Route::get('/user/contract/aoa/{id}',   [UserContractManageController::class,'aoa_index']);
        Route::get('/user/contract/aoa/view/{id}',   [UserContractManageController::class,'aoa_view']);
        Route::post('/user/contract/aoa/sign/{id}',   [UserContractManageController::class,'aoa_sign']);
        Route::get('/user/contract/aoa/create/{id}',   [UserContractManageController::class,'aoa_create']);
        Route::post('/user/contract/aoa/store/{id}',   [UserContractManageController::class,'aoa_store']);
        Route::get('/user/contract/aoa/edit/{id}',   [UserContractManageController::class,'aoa_edit']);
        Route::post('/user/contract/aoa/update/{id}',   [UserContractManageController::class,'aoa_update']);
        Route::get('/user/contract/aoa/delete/{id}',   [UserContractManageController::class,'aoa_delete']);
        Route::post('/user/contract/aoa/send-mail/',   [UserContractManageController::class,'aoa_sendToMail']);

        Route::get('/user/merchant/index', [MerchantController::class,'index']);
        Route::get('/user/merchant/setting/{tab?}', [MerchantController::class,'setting']);
        Route::post('/user/merchant/setting/{tab?}', [MerchantController::class,'setting_update']);

        Route::get('/user/merchant/money-request', [MerchantMoneyRequestController::class,'index']);
        Route::get('/user/merchant/money-request/create', [MerchantMoneyRequestController::class,'create']);
        Route::post('/user/merchant/money-request/store', [MerchantMoneyRequestController::class,'store']);
        Route::get('/user/merchant/money-request/details/{id}', [MerchantMoneyRequestController::class,'details']);

        Route::get('/user/merchant/shop', [MerchantShopController::class,'index']);
        Route::post('/user/merchant/shop/store', [MerchantShopController::class,'store']);
        Route::get('/user/merchant/shop/edit/{id}', [MerchantShopController::class,'edit']);
        Route::post('/user/merchant/shop/update/{id}', [MerchantShopController::class,'update']);
        Route::get('/user/merchant/shop/delete/{id}', [MerchantShopController::class,'delete']);
        Route::get('/user/merchant/shop/{id}/view/product', [MerchantShopController::class,'view_products']);

        Route::get('/user/merchant/product', [MerchantProductController::class,'index']);
        Route::post('/user/merchant/product/store', [MerchantProductController::class,'store']);
        Route::post('/user/merchant/product/category/create', [MerchantProductController::class,'category_create']);
        Route::get('/user/merchant/product/edit/{id}', [MerchantProductController::class,'edit']);
        Route::post('/user/merchant/product/update/{id}', [MerchantProductController::class,'update']);
        Route::get('/user/merchant/product/delete/{id}', [MerchantProductController::class,'delete']);
        Route::get('/user/merchant/product/status/{id}', [MerchantProductController::class,'status']);
        Route::post('/user/merchant/product/pay', [MerchantProductController::class,'pay']);
        Route::get('/user/merchant/product/crypto/{id}', [MerchantProductController::class,'crypto']);
        Route::get('/user/merchant/product/crypto/pay/{id}', [MerchantProductController::class,'crypto_pay']);
        Route::get('/user/merchant/product/order', [MerchantProductController::class,'order']);
        Route::post('/user/merchant/product/send_email', [MerchantProductController::class,'send_email']);
        Route::get('/user/merchant/product/order/{id}', [MerchantProductController::class,'order_by_product']);

        Route::get('/user/pincode', [UserPincodeController::class,'index']);
        Route::post('/user/telegram/generate', [UserPincodeController::class,'tele_generate']);
        Route::post('/user/whatsapp/generate', [UserPincodeController::class,'whs_generate']);

        Route::get('/user/admin/tickets', [MessageController::class,'adminmessages']);
        Route::get('/user/admin/ticket/{id}', [MessageController::class,'adminmessage']);
        Route::post('/user/admin/ticket/post', [MessageController::class,'adminpostmessage']);
        Route::post('/user/admin/user/send/ticket', [MessageController::class,'adminusercontact']);
        Route::get('/user/admin/ticket/status/{id}/{status}', [MessageController::class,'ticket_status']);

        Route::post('user/fetch-withdraw-list', [UserWithdrawController::class, 'withdraw']);
        Route::post('user/withdraw-create', [UserWithdrawController::class, 'withdrawcreate']);
        Route::post('user/withdraw-details', [UserWithdrawController::class, 'withdrawdetails']);

        Route::post('user/withdrawbank', [UserWithdrawBankController::class, 'withdrawbank']);

        Route::post('user/deposit', [UserDepositController::class, 'deposit']);
        Route::post('user/deposit-details', [UserDepositController::class, 'depositdetails']);

        Route::post('user/transactions', [TransactionController::class, 'transactions']);
        Route::post('user/transfer-logs', [TransactionController::class, 'transferlogs']);


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
