<?php
use App\Http\Controllers;
use App\Http\Controllers\Deposit\AuthorizeController;
use App\Http\Controllers\Deposit\InstamojoController;
use App\Http\Controllers\Deposit\MollieController;
use App\Http\Controllers\Deposit\PaypalController;
use App\Http\Controllers\Deposit\PaytmController;
use App\Http\Controllers\Deposit\RazorpayController;
use App\Http\Controllers\Deposit\StripeController;
use App\Http\Controllers\User\DepositController;
use App\Http\Controllers\User\DepositBankController;
use App\Http\Controllers\User\BeneficiaryController;
use App\Http\Controllers\Deposit\FlutterwaveController;
use App\Http\Controllers\Deposit\ManualController;
use App\Http\Controllers\Deposit\PaystackController;
use App\Http\Controllers\User\ForgotController;
use App\Http\Controllers\User\KYCController;
use App\Http\Controllers\User\MessageController;
use App\Http\Controllers\User\MoneyRequestController;
use App\Http\Controllers\User\OTPController;
use App\Http\Controllers\User\PricingPlanController;
use App\Http\Controllers\User\ReferralController;
use App\Http\Controllers\User\RegisterController;
use App\Http\Controllers\User\SendController;
use App\Http\Controllers\User\TransferLogController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\UserDpsController;
use App\Http\Controllers\User\UserFdrController;
use App\Http\Controllers\User\UserLoanController;
use App\Http\Controllers\User\WireTransferController;
use App\Http\Controllers\User\WithdrawController;
use App\Http\Controllers\User\VoucherController;
use App\Http\Middleware\KYC;
use App\Http\Middleware\Otp;
use App\Models\Childcategory;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Subscription\AuthorizeController as SubAuthorizeController;
use App\Http\Controllers\Subscription\FlutterwaveController as SubFlutterwaveController;
use App\Http\Controllers\Subscription\InstamojoController as SubInstamojoController;
use App\Http\Controllers\Subscription\MollieController as SubMollieController;
use App\Http\Controllers\Subscription\PaypalController as SubPaypalController;
use App\Http\Controllers\Subscription\PaytmController as SubPaytmController;
use App\Http\Controllers\Subscription\RazorpayController as SubRazorpayController;
use App\Http\Controllers\Subscription\StripeController as SubStripeController;
use App\Http\Controllers\Subscription\SubscriptionController;
use App\Http\Controllers\User\DashboardController as AppDashboardController;
use App\Http\Controllers\User\OtherBankController as UserOtherBankController;
use App\Http\Controllers\User\LoginController as UserLoginController;
use App\Http\Controllers\User\ManageInvoiceController;
use App\Http\Controllers\User\EscrowController;
use App\Http\Controllers\User\TransferController;
use App\Http\Controllers\User\ExchangeMoneyController;
use App\Http\Controllers\User\MerchantController;
use App\Http\Controllers\User\MerchantShopController;
use App\Http\Controllers\User\MerchantSendController;
use App\Http\Controllers\User\MerchantMoneyRequestController;
use App\Http\Controllers\User\MerchantOtherBankController;
use App\Http\Controllers\User\UserOpenPaydController;
use App\Http\Controllers\User\UserRailsbankController;
use App\Http\Controllers\User\SupervisorController;
use App\Http\Controllers\User\OwnTransferController;
use App\Http\Controllers\User\UserInvestmentController;
use App\Http\Controllers\User\UserClearJunctionController;
use App\Http\Controllers\User\UserEMbankController;
use App\Http\Controllers\User\UserTreezorBankController;
use App\Http\Controllers\User\UserBankableController;
use App\Http\Controllers\User\UserGlobalPassController;
use App\Http\Controllers\Deposit\RailsBankController;
use App\Http\Controllers\Deposit\OpenPaydController;
use App\Http\Controllers\User\UserContractManageController;

Route::prefix('user')->group(function() {

    Route::get('/login', [UserLoginController::class,'showLoginForm'])->name('user.login');
    Route::post('/login', [UserLoginController::class,'login'])->name('user.login.submit');

    Route::get('/otp', [OTPController::class,'showotpForm'])->name('user.otp');
    Route::post('/otp', [OTPController::class,'otp'])->name('user.otp.submit');

    Route::get('/register', [RegisterController::class,'showRegisterForm'])->name('user.register');
    Route::post('/register', [RegisterController::class,'register'])->name('user.register.submit');

    Route::get('/domain-register/{id}', [RegisterController::class,'showDomainRegisterForm'])->name('user.domain.register');
    Route::post('/domain-register/{id}', [RegisterController::class,'domainRegister'])->name('user.domain.register.submit');

    Route::get('/register/verify/{token}', [RegisterController::class,'token'])->name('user.register.token');

    Route::group(['middleware' => ['otp','banuser']],function () {

      Route::get('/dashboard', [UserController::class,'index'])->name('user.dashboard');
      Route::get('/username/{number}', [UserController::class,'username'])->name('user.username');
      Route::get('/username-by-email/{email}', [UserController::class,'username_by_email'])->name('user.username.email');
      Route::get('/transactions', [UserController::class,'transaction'])->name('user.transaction');
      Route::get('/transactions-export', [UserController::class,'transactionExport'])->name('user.transaction-export');
      Route::get('/transactions-pdf', [UserController::class,'transactionPDF'])->name('user.transaction-pdf');
      Route::get('/transaction/details/{id}', [UserController::class,'trxDetails'])->name('user.trxDetails');

      Route::get('/export-pdf', [UserController::class,'generatePDF'])->name('user.export.pdf');

      Route::get('/two-factor', [UserController::class,'showTwoFactorForm'])->name('user.show2faForm');
      Route::post('/createTwoFactor', [UserController::class,'createTwoFactor'])->name('user.createTwoFactor');
      Route::post('/disableTwoFactor', [UserController::class,'disableTwoFactor'])->name('user.disableTwoFactor');

      Route::post('check-receiver', [TransferController::class,'checkReceiver'])->name('user.check.receiver');

      Route::get('/profile', [UserController::class,'profile'])->name('user.profile.index');
      Route::post('/profile', [UserController::class,'profileupdate'])->name('user.profile.update');

      Route::get('/forgot', [ForgotController::class,'showforgotform'])->name('user.forgot');
      Route::post('/forgot', [ForgotController::class,'forgot'])->name('user.forgot.submit');

      Route::get('/kyc-form', [KYCController::class,'kycform'])->name('user.kyc.form');
      Route::post('/kyc-form', [KYCController::class,'kyc'])->name('user.kyc.submit');

      Route::group(['middleware'=>'kyc:Loan'],function(){
        Route::get('/loans', [UserLoanController::class,'index'])->name('user.loans.index');
        // Route::get('/pending-loans', [UserLoanController::class,'pending'])->name('user.loans.pending');
        // Route::get('/running-loans', [UserLoanController::class,'running'])->name('user.loans.running');
        // Route::get('/paid-loans', [UserLoanController::class,'paid'])->name('user.loans.paid');
        // Route::get('/rejected-loans', [UserLoanController::class,'rejected'])->name('user.loans.rejected');
        Route::get('/loan-plan', [UserLoanController::class,'loanPlan'])->name('user.loans.plan');
        Route::post('/loan-amount', [UserLoanController::class,'loanAmount'])->name('user.loan.amount');
        Route::post('/loan-request', [UserLoanController::class,'loanRequest'])->name('user.loan.request');
        Route::post('/loans/finish', [UserLoanController::class,'loanfinish'])->name('user.loan.finish');
        Route::get('/loan-logs/{id}', [UserLoanController::class,'log'])->name('user.loans.logs');
      });
      Route::get('/invest', [UserInvestmentController::class,'index'])->name('user.invest.index');

      Route::post('/dps-plan', [UserDpsController::class,'planDetails'])->name('user.dps.planDetails');
      Route::post('/dps-submit', [UserDpsController::class,'dpsSubmit'])->name('user.loan.dpsSubmit');
      Route::post('/dps/finish', [UserDpsController::class,'finish'])->name('user.dps.finish');
      Route::get('/dps-logs/{id}', [UserDpsController::class,'log'])->name('user.dps.logs');

      Route::post('/fdr-amount', [UserFdrController::class,'fdrAmount'])->name('user.fdr.amount');
      Route::post('/fdr-request', [UserFdrController::class,'fdrRequest'])->name('user.fdr.request');
      Route::post('/fdr/finish', [UserFdrController::class,'finish'])->name('user.fdr.finish');

      Route::get('/merchant/index', [MerchantController::class,'index'])->name('user.merchant.index');
      Route::get('/merchant/download-qr/{email}',  [MerchantController::class,'downloadQR'])->name('user.merchant.download.qr');
      Route::get('/merchant/send-money',[MerchantSendController::class,'create'])->name('user.merchant.send.money.create');
      Route::post('/merchant/send-money',[MerchantSendController::class,'store'])->name('user.merchant.send.money.store');
      Route::get('/merchant/send/money/success',[MerchantSendController::class,'success'])->name('user.merchant.send.money.success');
      Route::get('/merchant/send/money/cancle',[MerchantSendController::class,'cancle'])->name('user.merchant.send.money.cancle');
      Route::get('/merchant/send-money/{number}',[MerchantSendController::class,'savedUser'])->name('user.merchant.send.money.savedUser');
      Route::post('/merchant/save-account',[MerchantSendController::class,'saveAccount'])->name('user.merchant.save.account');

      Route::get('/merchant/money-request', [MerchantMoneyRequestController::class,'index'])->name('user.merchant.money.request.index');
      Route::get('/merchant/request-money/receive',[MerchantMoneyRequestController::class,'receive'])->name('user.merchant.request.money.receive');
      Route::get('/merchant/money-request/create', [MerchantMoneyRequestController::class,'create'])->name('user.merchant.money.request.create');
      Route::post('/merchant/money-request/store', [MerchantMoneyRequestController::class,'store'])->name('user.merchant.money.request.store');
      Route::get('/merchant/money-request/details/{id}', [MerchantMoneyRequestController::class,'details'])->name('user.merchant.money.request.details');

      Route::get('/merchant/other-bank',[MerchantOtherBankController::class,'index'])->name('user.merchant.other.bank');
      Route::get('/merchant/other-bank/{id}',[MerchantOtherBankController::class,'othersend'])->name('user.merchant.other.send');
      Route::post('/merchant/other-bank/store', [MerchantOtherBankController::class,'store'])->name('user.merchant.other.send.store');

      Route::get('/merchant/shop', [MerchantShopController::class,'index'])->name('user.merchant.shop.index');
      Route::get('/merchant/shop/create', [MerchantShopController::class,'create'])->name('user.merchant.shop.create');
      Route::post('/merchant/shop/store', [MerchantShopController::class,'store'])->name('user.merchant.shop.store');
      Route::get('/merchant/shop/edit/{id}', [MerchantShopController::class,'edit'])->name('user.merchant.shop.edit');
      Route::post('/merchant/shop/update/{id}', [MerchantShopController::class,'update'])->name('user.merchant.shop.update');
      Route::get('/merchant/shop/delete/{id}', [MerchantShopController::class,'delete'])->name('user.merchant.shop.delete');



      Route::group(['middleware'=>'kyc:Request Money'],function(){
        Route::get('/money-request', [MoneyRequestController::class,'index'])->name('user.money.request.index');
        Route::get('/request-money/receive',[MoneyRequestController::class,'receive'])->name('user.request.money.receive');
        Route::get('/money-request/create', [MoneyRequestController::class,'create'])->name('user.money.request.create');
        Route::post('/money-request/store', [MoneyRequestController::class,'store'])->name('user.money.request.store');
        Route::get('/request/money/verify/{id}', [MoneyRequestController::class,'verify'])->name('user.request.money.verify');
        Route::post('/request/money/verify/{id}', [MoneyRequestController::class,'verify'])->name('user.request.money.verify');
        Route::post('/request/money/send/{id}', [MoneyRequestController::class,'send'])->name('user.request.money.send');
        Route::post('/request/money/cancel/{id}', [MoneyRequestController::class,'cancel'])->name('user.request.money.cancel');
        Route::get('/money-request/details/{id}', [MoneyRequestController::class,'details'])->name('user.money.request.details');
      });

       //exchange money
      Route::group(['middleware'=>'kyc:Exchange'],function(){
        Route::get('exchange-money',   [ExchangeMoneyController::class,'exchangeForm'])->name('user.exchange.money');
        Route::post('exchange-money',  [ExchangeMoneyController::class,'submitExchange']);
      });
      Route::get('exchange-money/history',  [ExchangeMoneyController::class,'exchangeHistory'])->name('user.exchange.history');
      Route::get('exchange-money/calcharge/{amount}',  [ExchangeMoneyController::class,'calcharge'])->name('user.exchange.calcharge');

       //invoice
       Route::group(['middleware'=>'kyc:Invoice'],function(){
          Route::get('create-invoice',   [ManageInvoiceController::class,'create'])->name('user.invoice.create');
          Route::post('create-invoice',   [ManageInvoiceController::class,'store']);
      });

      //Voucher
      Route::group(['middleware'=>'kyc:Voucher'],function(){
        Route::get('create-voucher',   [VoucherController::class,'create'])->name('user.create.voucher');
        Route::post('create-voucher',   [VoucherController::class,'submit']);
      });

       //escrow
      Route::group(['middleware'=>'kyc:Escrow'],function(){
        Route::get('make-escrow',   [EscrowController::class,'create'])->name('user.escrow.create');
        Route::post('make-escrow',   [EscrowController::class,'store']);
        Route::get('escrow/calcharge/{amount}',   [EscrowController::class,'calcharge']);
      });

      //Reedem voucher
      Route::get('vouchers',  [VoucherController::class,'vouchers'])->name('user.vouchers');
      Route::get('reedem-voucher',  [VoucherController::class,'reedemForm'])->name('user.reedem.voucher');
      Route::post('reedem-voucher',  [VoucherController::class,'reedemSubmit'])->name('user.reedemSubmit');
      Route::get('reedemed-history',  [VoucherController::class,'reedemHistory'])->name('user.reedem.history');

      //invoice
      Route::get('invoices',   [ManageInvoiceController::class,'index'])->name('user.invoice.index');
      Route::post('invoice/pay-status',   [ManageInvoiceController::class,'payStatus'])->name('user.invoice.pay.status');
      Route::post('invoice/publish-status',   [ManageInvoiceController::class,'publishStatus'])->name('user.invoice.publish.status');

      Route::get('invoices-edit/{id}',   [ManageInvoiceController::class,'edit'])->name('user.invoice.edit');
      Route::post('invoices-update/{id}',   [ManageInvoiceController::class,'update'])->name('user.invoice.update');
      Route::get('invoice-cancel/{id}',   [ManageInvoiceController::class,'cancel'])->name('user.invoice.cancel');
      Route::get('invoice/send-mail/{id}',   [ManageInvoiceController::class,'sendToMail'])->name('user.invoice.send.mail');
      Route::get('invoice/view/{number}',   [ManageInvoiceController::class,'view'])->name('user.invoice.view');

      Route::get('invoices-payment/{number}',   [ManageInvoiceController::class,'invoicePayment'])->name('user.invoice.payment');
      Route::post('invoices-payment/{number}',   [ManageInvoiceController::class,'invoicePaymentSubmit']);
      Route::get('contract',   [UserContractManageController::class,'index'])->name('user.contract.index');
      Route::get('contract/create',   [UserContractManageController::class,'create'])->name('user.contract.create');
      Route::post('contract/store',   [UserContractManageController::class,'store'])->name('user.contract.store');
      Route::get('contract/edit/{id}',   [UserContractManageController::class,'edit'])->name('user.contract.edit');
      Route::post('contract/update/{id}',   [UserContractManageController::class,'update'])->name('user.contract.update');
      Route::get('contract/delete/{id}',   [UserContractManageController::class,'delete'])->name('user.contract.delete');
      Route::get('contract/aoa/{id}',   [UserContractManageController::class,'aoa_index'])->name('user.contract.aoa');
      Route::get('contract/aoa/create/{id}',   [UserContractManageController::class,'aoa_create'])->name('user.contract.aoa.create');
      Route::post('contract/aoa/store/{id}',   [UserContractManageController::class,'aoa_store'])->name('user.contract.aoa.store');
      Route::get('contract/aoa/edit/{id}',   [UserContractManageController::class,'aoa_edit'])->name('user.contract.aoa.edit');
      Route::post('contract/aoa/update/{id}',   [UserContractManageController::class,'aoa_update'])->name('user.contract.aoa.update');
      Route::get('contract/aoa/delete/{id}',   [UserContractManageController::class,'aoa_delete'])->name('user.contract.aoa.delete');
      Route::get('pay-invoice',   [DepositController::class,'invoicePayment'])->name('user.pay.invoice');

      //escrow
      Route::get('my-escrow',   [EscrowController::class,'index'])->name('user.escrow.index');
      Route::get('escrow-pending',   [EscrowController::class,'pending'])->name('user.escrow.pending');
      Route::get('escrow-dispute/{id}',   [EscrowController::class,'disputeForm'])->name('user.escrow.dispute');
      Route::post('escrow-dispute/{id}',   [EscrowController::class,'disputeStore']);
      Route::get('release-escrow/{id}',   [EscrowController::class,'release'])->name('user.escrow.release');
      Route::get('file-download/{id}',   [EscrowController::class,'fileDownload'])->name('user.escrow.file.download');

      Route::group(['middleware'=>'kyc:Wire Transfer'],function(){
        Route::get('wire-transfer',[WireTransferController::class,'index'])->name('user.wire.transfer.index');
        Route::get('wire-transfer/create',[WireTransferController::class,'create'])->name('user.wire.transfer.create');
        Route::post('wire-transfer/store',[WireTransferController::class,'store'])->name('user.wire.transfer.store');
        Route::get('/wire-transfers/show/{id}', [WireTransferController::class,'show'])->name('user.wire.transfer.show');
      });

      Route::group(['middleware'=>'kyc:Withdraw'],function(){
        Route::get('/withdraw', [WithdrawController::class,'index'])->name('user.withdraw.index');
        Route::get('/withdraw/create', [WithdrawController::class,'create'])->name('user.withdraw.create');
        Route::post('/withdraw/store', [WithdrawController::class,'store'])->name('user.withdraw.store');
        Route::get('/withdraw/{id}', [WithdrawController::class,'details'])->name('user.withdraw.details');
        Route::POST('/withdraw/gateway',[WithdrawController::class,'gateway'])->name('user.withdraw.gateway');
        Route::POST('/withdraw/gatewaycurrency',[WithdrawController::class,'gatewaycurrency'])->name('user.withdraw.gatewaycurrency');
    });

      Route::group(['middleware'=>'kyc:Transfer'],function(){
        Route::get('/send-money',[SendController::class,'create'])->name('send.money.create');
        // Route::post('/send-money-two-auth',[SendController::class,'storeTwoAuth'])->name('send.money.store-two-auth');
        Route::post('/send-money',[SendController::class,'store'])->name('send.money.store');
        Route::get('/send/money/success',[SendController::class,'success'])->name('user.send.money.success');
        Route::get('/send/money/cancle',[SendController::class,'cancle'])->name('user.send.money.cancle');
        Route::get('/send-money/{number}',[SendController::class,'savedUser'])->name('send.money.savedUser');
        Route::post('/save-account',[SendController::class,'saveAccount'])->name('user.save.account');

        Route::get('tranfer-logs',[TransferLogController::class,'index'])->name('tranfer.logs.index');

        Route::get('/other-bank',[UserOtherBankController::class,'index'])->name('user.other.bank');
        Route::get('/other-bank/{id}',[UserOtherBankController::class,'othersend'])->name('user.other.send');
        Route::post('/other-bank/store', [UserOtherBankController::class,'store'])->name('user.other.send.store');

        Route::get('/beneficiaries', [BeneficiaryController::class,'index'])->name('user.beneficiaries.index');
        Route::get('/beneficiaries/create', [BeneficiaryController::class,'create'])->name('user.beneficiaries.create');
        Route::post('/beneficiaries/store', [BeneficiaryController::class,'store'])->name('user.beneficiaries.store');
        Route::get('/beneficiaries/show/{id}', [BeneficiaryController::class,'show'])->name('user.beneficiaries.show');

        Route::get('/openpayd/createaccount', [UserOpenPaydController::class, 'CreateAccount'])->name('user.openpayd.api.createaccount');
        Route::get('/openpayd/getaccountlist', [UserOpenPaydController::class, 'GetAccountList'])->name('user.openpayd.api.getaccountlist');
        Route::get('/openpayd/getaccount/{id}', [UserOpenPaydController::class, 'GetAccount'])->name('user.openpayd.api.getaccount');
        Route::get('/openpayd/getbanklist', [UserOpenPaydController::class, 'GetBankList'])->name('user.openpayd.api.getbanklist');
        Route::get('/openpayd/createbeneficiary', [UserOpenPaydController::class, 'CreateBeneficiary'])->name('user.openpayd.api.createbeneficiary');
        Route::get('/openpayd/getbeneficiaries', [UserOpenPaydController::class, 'GetBeneficiaries'])->name('user.openpayd.api.getbeneficiaries');
        Route::get('/openpayd/getbeneficiary/{id}', [UserOpenPaydController::class, 'GetBeneficiary'])->name('user.openpayd.api.getbeneficiary');
        Route::get('/openpayd/createbankbeneficiary', [UserOpenPaydController::class, 'CreateBankBeneficiary'])->name('user.openpayd.api.createbankbeneficiary');
        Route::get('/openpayd/getbankbeneficiarylist', [UserOpenPaydController::class, 'GetBankBeneficiaryList'])->name('user.openpayd.api.getbankbeneficiarylist');
        Route::get('/openpayd/getbankbeneficiary/{id}', [UserOpenPaydController::class, 'GetBankBeneficiary'])->name('user.openpayd.api.getbankbeneficiary');
        Route::get('/openpayd/createbankpayout', [UserOpenPaydController::class, 'CreateBankPayout'])->name('user.openpayd.api.createbankpayout');
        Route::get('/openpayd/createinternaltransfer', [UserOpenPaydController::class, 'CreateInternalTransfer'])->name('user.openpayd.api.createinternaltransfer');
        Route::get('/openpayd/gettransaction/{id}', [UserOpenPaydController::class, 'GetTransaction'])->name('user.openpayd.api.gettransaction');
        Route::get('/openpayd/gettransactionlist', [UserOpenPaydController::class, 'GetTransactionList'])->name('user.openpayd.api.gettransactionlist');

        Route::get('/railsbank/getenduserlist', [UserRailsbankcontroller::class, 'GetEnduserList'])->name('user.railsbank.api.getenduserlist');
        Route::get('/railsbank/createenduser', [UserRailsbankcontroller::class, 'CreateEnduser'])->name('user.railsbank.api.createenduser');
        Route::get('/railsbank/checkenduserstatus/{id}', [UserRailsbankcontroller::class, 'CheckEnduserStatus'])->name('user.railsbank.api.checkenduserstatus');
        Route::get('/railsbank/createledger', [UserRailsbankcontroller::class, 'CreateLedger'])->name('user.railsbank.api.createledger');
        Route::get('/railsbank/getledgerlist', [UserRailsbankcontroller::class, 'GetLegderList'])->name('user.railsbank.api.getledgerlist');
        Route::get('/railsbank/getledger/{id}', [UserRailsbankcontroller::class, 'GetLegder'])->name('user.railsbank.api.getledger');
        Route::get('/railsbank/assigniban/{id}', [UserRailsbankcontroller::class, 'Assigniban'])->name('user.railsbank.api.assigniban');
        Route::get('/railsbank/gettransaction/{id}', [UserRailsbankcontroller::class, 'GetTransaction'])->name('user.railsbank.api.gettransaction');
        Route::get('/railsbank/gettransactionlist', [UserRailsbankcontroller::class, 'GetTransactionList'])->name('user.railsbank.api.gettransactionlist');
        Route::get('/railsbank/createbeneficiary', [UserRailsbankcontroller::class, 'CreateBeneficiary'])->name('user.railsbank.api.createbeneficiary');
        Route::get('/railsbank/getbeneficiarylist', [UserRailsbankcontroller::class, 'GetBeneficiaryList'])->name('user.railsbank.api.getbeneficiarylist');
        Route::get('/railsbank/getbeneficiary/{id}', [UserRailsbankcontroller::class, 'GetBeneficiary'])->name('user.railsbank.api.getbeneficiary');
        Route::get('/railsbank/createtransfer', [UserRailsbankcontroller::class, 'CreateTransfer'])->name('user.railsbank.api.createtransfer');

        Route::get('/clearjunction/bankwallet', [UserClearJunctionController::class, 'CheckBankWallet'])->name('user.clearjunction.api.bankwallet');
        Route::get('/clearjunction/walletstatement', [UserClearJunctionController::class, 'GetWalletStatement'])->name('user.clearjunction.api.walletstatement');
        Route::get('/clearjunction/ibanindividual', [UserClearJunctionController::class, 'AllocateIbanIndividual'])->name('user.clearjunction.api.ibanindividual');
        Route::get('/clearjunction/ibancreate', [UserClearJunctionController::class, 'AllocateIbanCreate'])->name('user.clearjunction.api.ibancreate');
        Route::get('/clearjunction/becscreate', [UserClearJunctionController::class, 'AllocateBecsCreate'])->name('user.clearjunction.api.becscreate');
        Route::get('/clearjunction/ibanstatusclientorder', [UserClearJunctionController::class, 'GetIbanStatusByClientOrder'])->name('user.clearjunction.api.ibanstatusclientorder');
        Route::get('/clearjunction/ibanstatusorderref', [UserClearJunctionController::class, 'GetIbanStatusByOrderRef'])->name('user.clearjunction.api.ibanstatusorderref');
        Route::get('/clearjunction/ibanlistbycid', [UserClearJunctionController::class, 'IbanListByCid'])->name('user.clearjunction.api.ibanlistbycid');
        Route::get('/clearjunction/becsbyorderref', [UserClearJunctionController::class, 'BecsByOrderRef'])->name('user.clearjunction.api.becsbyorderref');
        Route::get('/clearjunction/becsbyclientorderid', [UserClearJunctionController::class, 'BecsByClientOrderid'])->name('user.clearjunction.api.becsbyclientorderid');
        Route::get('/clearjunction/tousbankswift', [UserClearJunctionController::class, 'ToUsBankSwift'])->name('user.clearjunction.api.tousbankswift');
        Route::get('/clearjunction/tousbankfedwire', [UserClearJunctionController::class, 'ToUsBankFedwire'])->name('user.clearjunction.api.tousbankfedwire');
        Route::get('/clearjunction/tousbanksignet', [UserClearJunctionController::class, 'ToUsBankSignet'])->name('user.clearjunction.api.tousbanksignet');
        Route::get('/clearjunction/internalpayment', [UserClearJunctionController::class, 'InternalPayment'])->name('user.clearjunction.api.internalpayment');
        Route::get('/clearjunction/eubanksct', [UserClearJunctionController::class, 'EuBankSCT'])->name('user.clearjunction.api.eubanksct');
        Route::get('/clearjunction/eubankinstant', [UserClearJunctionController::class, 'EuBankInstant'])->name('user.clearjunction.api.eubankinstant');
        Route::get('/clearjunction/ukbankfaster', [UserClearJunctionController::class, 'UkBankFaster'])->name('user.clearjunction.api.ukbankfaster');
        Route::get('/clearjunction/ukbankchaps', [UserClearJunctionController::class, 'UkBankChaps'])->name('user.clearjunction.api.ukbankchaps');
        Route::get('/clearjunction/aubankbecs', [UserClearJunctionController::class, 'AuBankBecs'])->name('user.clearjunction.api.aubankbecs');
        Route::get('/clearjunction/uabankpayout', [UserClearJunctionController::class, 'UaBankPayout'])->name('user.clearjunction.api.uabankpayout');
        Route::get('/clearjunction/mdbankpayout', [UserClearJunctionController::class, 'MdBankPayout'])->name('user.clearjunction.api.mdbankpayout');
        Route::get('/clearjunction/creditcardpayout', [UserClearJunctionController::class, 'CreditCardPayout'])->name('user.clearjunction.api.creditcardpayout');
        Route::get('/clearjunction/payoutstatusclientorder', [UserClearJunctionController::class, 'PayoutStatusClientOrder'])->name('user.clearjunction.api.payoutstatusclientorder');

        //payin clear junction route
        Route::get('/clearjunction/createinvoice', [UserClearJunctionController::class, 'CreateInvoice'])->name('user.clearjunction.api.createinvoice');
        Route::get('/clearjunction/invoicestatusbyref', [UserClearJunctionController::class, 'InvoiceStatusByRef'])->name('user.clearjunction.api.invoicestatusbyref');
        Route::get('/clearjunction/invoicestatusbyorder', [UserClearJunctionController::class, 'InvoiceStatusByOrder'])->name('user.clearjunction.api.invoicestatusbyorder');
        //Transaction rote
        Route::get('/clearjunction/transactionapprove', [UserClearJunctionController::class, 'TransactionApprove'])->name('user.clearjunction.api.transactionapprove');
        Route::get('/clearjunction/transactioncancel', [UserClearJunctionController::class, 'TransactionCancel'])->name('user.clearjunction.api.transactioncancel');
        //Tokenize
        Route::get('/clearjunction/createtoken', [UserClearJunctionController::class, 'CreateToken'])->name('user.clearjunction.api.createtoken');
        //Check Requisite
        Route::get('/clearjunction/checkrequibyiban', [UserClearJunctionController::class, 'CheckRequiByIBAN'])->name('user.clearjunction.api.checkrequibyiban');
        ///report
        Route::get('/clearjunction/transactionreport', [UserClearJunctionController::class, 'TransactionReport'])->name('user.clearjunction.api.transactionreport');
        // wallet
        Route::get('/clearjunction/createinvidualwallet', [UserClearJunctionController::class, 'CreateInvidualWallet'])->name('user.clearjunction.api.createinvidualwallet');
        Route::get('/clearjunction/wallettransfer', [UserClearJunctionController::class, 'WalletTransfer'])->name('user.clearjunction.api.wallettransfer');
        Route::get('/clearjunction/transferstatusorderref', [UserClearJunctionController::class, 'TransferStatusOrderRef'])->name('user.clearjunction.api.transferstatusorderref');
        Route::get('/clearjunction/transferstatusclientorder', [UserClearJunctionController::class, 'TransferStatusClientOrder'])->name('user.clearjunction.api.transferstatusclientorder');
        Route::get('/clearjunction/clientwalletbalance', [UserClearJunctionController::class, 'clientwalletbalance'])->name('user.clearjunction.api.clientwalletbalance');
        //Reserve Individual wallet
        Route::get('/clearjunction/reserveindividualwallet', [UserClearJunctionController::class, 'ReserveIndividualWallet'])->name('user.clearjunction.api.reserveindividualwallet');
        Route::get('/clearjunction/reservecorporatewallet', [UserClearJunctionController::class, 'ReserveCorporateWallet'])->name('user.clearjunction.api.reservecorporatewallet');
        Route::get('/clearjunction/reservestatusbyorderref', [UserClearJunctionController::class, 'ReserveStatusByOrderRef'])->name('user.clearjunction.api.reservestatusbyorderref');
        Route::get('/clearjunction/reservestatusbyclientorderid', [UserClearJunctionController::class, 'ReserveStatusByClientOrderID'])->name('user.clearjunction.api.reservestatusbyclientorderid');
        //Entity Partner
        Route::get('/clearjunction/corporateentity', [UserClearJunctionController::class, 'CorporateEntity'])->name('user.clearjunction.api.corporateentity');
        Route::get('/clearjunction/corporateuaentity', [UserClearJunctionController::class, 'CorporateUaEntity'])->name('user.clearjunction.api.corporateuaentity');
        Route::get('/clearjunction/individualbecsentity', [UserClearJunctionController::class, 'IndividualBecsEntity'])->name('user.clearjunction.api.individualbecsentity');
        Route::get('/clearjunction/individualinternalpaymententity', [UserClearJunctionController::class, 'IndividualInternalPaymentEntity'])->name('user.clearjunction.api.individualinternalpaymententity');
        Route::get('/clearjunction/corporatebecsentity', [UserClearJunctionController::class, 'CorporateBecsEntity'])->name('user.clearjunction.api.corporatebecsentity');
        Route::get('/clearjunction/individualusentity', [UserClearJunctionController::class, 'IndividualUsEntity'])->name('user.clearjunction.api.individualusentity');
        Route::get('/clearjunction/individualeuentity', [UserClearJunctionController::class, 'IndividualEuEntity'])->name('user.clearjunction.api.individualeuentity');
        Route::get('/clearjunction/individualuaentity', [UserClearJunctionController::class, 'IndividualUaEntity'])->name('user.clearjunction.api.individualuaentity');
        Route::get('/clearjunction/individualmdentity', [UserClearJunctionController::class, 'IndividualMdEntity'])->name('user.clearjunction.api.individualmdentity');
        //Entity Payment Details
        Route::get('/clearjunction/banktransferswiftpaymentdetailentity', [UserClearJunctionController::class, 'BankTransferSwiftPaymentDetailEntity'])->name('user.clearjunction.api.banktransferswiftpaymentdetailentity');
        Route::get('/clearjunction/banktransferfedwirepaymentdetailentity', [UserClearJunctionController::class, 'BankTransferFedwirePaymentDetailEntity'])->name('user.clearjunction.api.banktransferfedwirepaymentdetailentity');
        Route::get('/clearjunction/signetpaymentdetailentity', [UserClearJunctionController::class, 'SignetPaymentDetailEntity'])->name('user.clearjunction.api.signetpaymentdetailentity');
        Route::get('/clearjunction/banktransfereupaymentdetailentity', [UserClearJunctionController::class, 'BankTransferEuPaymentDetailEntity'])->name('user.clearjunction.api.banktransfereupaymentdetailentity');
        Route::get('/clearjunction/internalpaymentdetailentity', [UserClearJunctionController::class, 'InternalPaymentDetailEntity'])->name('user.clearjunction.api.internalpaymentdetailentity');
        Route::get('/clearjunction/banktransfersepainstpaymentdetailentity', [UserClearJunctionController::class, 'BankTransferSepaInstPaymentDetailEntity'])->name('user.clearjunction.api.banktransfersepainstpaymentdetailentity');
        Route::get('/clearjunction/banktransferukfpspaymentdetailentity', [UserClearJunctionController::class, 'BankTransferUkFpsPaymentDetailEntity'])->name('user.clearjunction.api.banktransferukfpspaymentdetailentity');
        Route::get('/clearjunction/banktransferukchapspaymentdetailentity', [UserClearJunctionController::class, 'BankTransferUkChapsPaymentDetailEntity'])->name('user.clearjunction.api.banktransferukchapspaymentdetailentity');
        Route::get('/clearjunction/banktransferukbacspaymentdetailentity', [UserClearJunctionController::class, 'BankTransferUkBacsPaymentDetailEntity'])->name('user.clearjunction.api.banktransferukbacspaymentdetailentity');
        Route::get('/clearjunction/banktransferukdefaultpaymentdetailentity', [UserClearJunctionController::class, 'BankTransferUkDefaultPaymentDetailEntity'])->name('user.clearjunction.api.banktransferukdefaultpaymentdetailentity');
        Route::get('/clearjunction/banktransferbecspaymentdetailentity', [UserClearJunctionController::class, 'BankTransferBecsPaymentDetailEntity'])->name('user.clearjunction.api.banktransferbecspaymentdetailentity');
        Route::get('/clearjunction/banktransfermdpaymentdetailentity', [UserClearJunctionController::class, 'BankTransferMdPaymentDetailEntity'])->name('user.clearjunction.api.banktransfermdpaymentdetailentity');
        Route::get('/clearjunction/banktransferuapaymentdetailentity', [UserClearJunctionController::class, 'BankTransferUaPaymentDetailEntity'])->name('user.clearjunction.api.banktransferuapaymentdetailentity');
        Route::get('/clearjunction/creditcardpaymentdetailentity', [UserClearJunctionController::class, 'CreditCardPaymentDetailEntity'])->name('user.clearjunction.api.creditcardpaymentdetailentity');
        //Entity Registrants
        Route::get('/clearjunction/allocateibanindividualentity', [UserClearJunctionController::class, 'AllocateIbanIndividualEntity'])->name('user.clearjunction.api.allocateibanindividualentity');
        Route::get('/clearjunction/allocateibancorporateentity', [UserClearJunctionController::class, 'AllocateIbanCorporateEntity'])->name('user.clearjunction.api.allocateibancorporateentity');
        Route::get('/clearjunction/allocatebecsindividual', [UserClearJunctionController::class, 'AllocateBecsIndividual'])->name('user.clearjunction.api.allocatebecsindividual');
        Route::get('/clearjunction/allocatebecscorporate', [UserClearJunctionController::class, 'AllocateBecsCorporate'])->name('user.clearjunction.api.allocatebecscorporate');


        /// EM Bank API
        Route::get('/embank/accounts', [UserEMbankController::class, 'Accounts'])->name('user.embank.api.accounts');
        Route::get('/embank/createconsent', [UserEMbankController::class, 'CreateConsent'])->name('user.embank.api.createconsent');
        Route::get('/embank/accountsdetails', [UserEMbankController::class, 'AccountsDetails'])->name('user.embank.api.accountsdetails');
        Route::get('/embank/accountsbalance', [UserEMbankController::class, 'AccountsBalance'])->name('user.embank.api.accountsbalance');
        Route::get('/embank/accounttransactions', [UserEMbankController::class, 'AccountTransactions'])->name('user.embank.api.accounttransactions');
        Route::get('/embank/accountparty', [UserEMbankController::class, 'AccountParty'])->name('user.embank.api.accountparty');
        Route::get('/embank/accountpartysummary', [UserEMbankController::class, 'AccountPartySummary'])->name('user.embank.api.accountpartysummary');
        Route::get('/embank/beneficiaries', [UserEMbankController::class, 'Beneficiaries'])->name('user.embank.api.beneficiaries');
        Route::get('/embank/deleteaccount', [UserEMbankController::class, 'DeleteAccount'])->name('user.embank.api.deleteaccount');
        Route::get('/embank/accountconsentsdetails', [UserEMbankController::class, 'AccountConsentsDetails'])->name('user.embank.api.accountconsentsdetails');

        // Treezor Bank API
        Route::get('/treezorbank/balances', [UserTreezorBankController::class, 'Balances'])->name('user.treezorbank.api.balances');
        Route::get('/treezorbank/createbankaccounts', [UserTreezorBankController::class, 'CreateBankAccounts'])->name('user.treezorbank.api.createbankaccounts');
        Route::get('/treezorbank/bankaccountdetails', [UserTreezorBankController::class, 'BankAccountDetails'])->name('user.treezorbank.api.bankaccountdetails');
        Route::get('/treezorbank/deletebankaccount', [UserTreezorBankController::class, 'DeleteBankAccount'])->name('user.treezorbank.api.deletebankaccount');
        Route::get('/treezorbank/beneficiaries', [UserTreezorBankController::class, 'Beneficiaries'])->name('user.treezorbank.api.beneficiaries');
        Route::get('/treezorbank/searchbeneficiaries', [UserTreezorBankController::class, 'SearchBeneficiaries'])->name('user.treezorbank.api.searchbeneficiaries');
        Route::get('/treezorbank/getbeneficiaries', [UserTreezorBankController::class, 'GetBeneficiaries'])->name('user.treezorbank.api.getbeneficiaries');
        Route::get('/treezorbank/updatebeneficiaries', [UserTreezorBankController::class, 'UpdateBeneficiaries'])->name('user.treezorbank.api.updatebeneficiaries');
        Route::get('/treezorbank/businesssearchs', [UserTreezorBankController::class, 'Businesssearchs'])->name('user.treezorbank.api.businesssearchs');
        Route::get('/treezorbank/businessinformations', [UserTreezorBankController::class, 'Businessinformations'])->name('user.treezorbank.api.businessinformations');

        // Bankable API
        Route::get('/bankable/balances', [UserBankableController::class, 'Balances'])->name('user.bankable.api.balances');
        Route::get('/bankable/createbankaccounts', [UserBankableController::class, 'CreateBankAccounts'])->name('user.bankable.api.createbankaccounts');
        Route::get('/bankable/bankaccountdetails', [UserBankableController::class, 'BankAccountDetails'])->name('user.bankable.api.bankaccountdetails');
        Route::get('/bankable/deletebankaccount', [UserBankableController::class, 'DeleteBankAccount'])->name('user.bankable.api.deletebankaccount');
        Route::get('/bankable/beneficiaries', [UserBankableController::class, 'Beneficiaries'])->name('user.bankable.api.beneficiaries');
        Route::get('/bankable/searchbeneficiaries', [UserBankableController::class, 'SearchBeneficiaries'])->name('user.bankable.api.searchbeneficiaries');
        Route::get('/bankable/getbeneficiaries', [UserBankableController::class, 'GetBeneficiaries'])->name('user.bankable.api.getbeneficiaries');
        Route::get('/bankable/updatebeneficiaries', [UserBankableController::class, 'UpdateBeneficiaries'])->name('user.bankable.api.updatebeneficiaries');
        Route::get('/bankable/businesssearchs', [UserBankableController::class, 'Businesssearchs'])->name('user.bankable.api.businesssearchs');
        Route::get('/bankable/businessinformations', [UserBankableController::class, 'Businessinformations'])->name('user.bankable.api.businessinformations');


        Route::post('/globalpass/callback', [UserGlobalPassController::class, 'callback'])->name('user.globalpass.api.callback');
        Route::get('/globalpass/getscreenidstatus/{screentoken}/{id}', [UserGlobalPassController::class, 'GetScreenIDStatus'])->name('user.globalpass.api.getscreenidstatus');
        Route::get('/globalpass/getscreenaddressstatus/{screentoken}/{id}', [UserGlobalPassController::class, 'GetScreenAddressStatus'])->name('user.globalpass.api.getscreenaddressstatus');
        Route::get('/globalpass/getscreeniddetails/{screentoken}', [UserGlobalPassController::class, 'GetScreenID'])->name('user.globalpass.api.getscreeniddetails');
        Route::get('/globalpass/getscreenaddressdetails/{screentoken}', [UserGlobalPassController::class, 'GetScreenAddress'])->name('user.globalpass.api.getscreenaddressdetails');
        Route::post('/globalpass/createforensicsanalysis/{screentoken}', [UserGlobalPassController::class, 'CreateForensicsAnalysis'])->name('user.globalpass.api.createforensicsanalysis');
        Route::get('/globalpass/getforensicsstatus/{screentoken}/{id}', [UserGlobalPassController::class, 'GetForensicsStatus'])->name('user.globalpass.api.getforensicsstatus');
        Route::post('/globalpass/createamlbusinessscreen/{screentoken}', [UserGlobalPassController::class, 'CreateAMLBusinessScreen'])->name('user.globalpass.api.createamlbusinessscreen');
        Route::get('/globalpass/getamlbusinessscreen/{screentoken}', [UserGlobalPassController::class, 'GetAMLBusinessScreen'])->name('user.globalpass.api.getamlbusinessscreen');
    });

      Route::get('/package',[PricingPlanController::class,'index'])->name('user.package.index');
      Route::get('/package/subscription/{id}',[PricingPlanController::class,'subscription'])->name('user.package.subscription');


      Route::get('/deposits',[DepositController::class,'index'])->name('user.deposit.index');
      Route::get('/deposit/create',[DepositController::class,'create'])->name('user.deposit.create');
      Route::POST('/deposit/gateway',[DepositController::class,'gateway'])->name('user.deposit.gateway');
      Route::POST('/deposit/gatewaycurrency',[DepositController::class,'gatewaycurrency'])->name('user.deposit.gatewaycurrency');

      Route::get('/bank/deposits',[DepositBankController::class,'index'])->name('user.depositbank.index');
      Route::get('/bank/deposit/create',[DepositBankController::class,'create'])->name('user.depositbank.create');
      Route::POST('/bank/deposit/store',[DepositBankController::class,'store'])->name('user.depositbank.store');
      Route::POST('/bank/deposit/gateway',[DepositBankController::class,'gateway'])->name('user.depositbank.gateway');
      Route::POST('/bank/deposit/railsbank',[RailsBankController::class,'transfer'])->name('user.depositbank.railsbank');
      Route::POST('/bank/deposit/openpayd',[OpenPaydController::class,'transfer'])->name('user.depositbank.openpayd');
      Route::get('/bank/deposit/bankcurrency/{id}',[DepositBankController::class,'bankcurrency'])->name('user.depositbank.bankcurrency');

      Route::post('/deposit/stripe-submit', [StripeController::class,'store'])->name('deposit.stripe.submit');

      Route::post('/deposit/paystack/submit', [PaystackController::class,'store'])->name('deposit.paystack.submit');

      Route::post('/paypal-submit', [PaypalController::class,'store'])->name('deposit.paypal.submit');
      Route::get('/paypal/deposit/notify', [PaypalController::class,'notify'])->name('deposit.paypal.notify');
      Route::get('/paypal/deposit/cancle', [PaypalController::class,'cancle'])->name('deposit.paypal.cancle');

      Route::post('/instamojo-submit',[InstamojoController::class,'store'])->name('deposit.instamojo.submit');
      Route::get('/instamojo-notify',[InstamojoController::class,'notify'])->name('deposit.instamojo.notify');

      Route::post('/deposit/paytm-submit', [PaytmController::class,'store'])->name('deposit.paytm.submit');
      Route::post('/deposit/paytm-callback', [PaytmController::class,'paytmCallback'])->name('deposit.paytm.notify');

      Route::post('/deposit/razorpay-submit', [RazorpayController::class,'store'])->name('deposit.razorpay.submit');
      Route::post('/deposit/razorpay-notify', [RazorpayController::class,'notify'])->name('deposit.razorpay.notify');

      Route::post('/deposit/molly-submit', [MollieController::class,'store'])->name('deposit.molly.submit');
      Route::get('/deposit/molly-notify', [MollieController::class,'notify'])->name('deposit.molly.notify');

      Route::post('/deposit/flutter/submit', [FlutterwaveController::class,'store'])->name('deposit.flutter.submit');
      Route::post('/deposit/flutter/notify', [FlutterwaveController::class,'notify'])->name('deposit.flutter.notify');

      Route::post('/authorize-submit', [AuthorizeController::class,'store'])->name('deposit.authorize.submit');
      Route::post('/deposit/manual-submit', [ManualController::class,'store'])->name('deposit.manual.submit');


      Route::post('/subscription/stripe-submit', [SubStripeController::class,'store'])->name('subscription.stripe.submit');
      Route::post('/subscription/free', [SubscriptionController::class,'store'])->name('subscription.free.submit');

      Route::post('/subscription/paypal-submit', [SubPaypalController::class,'store'])->name('subscription.paypal.submit');
      Route::get('/subscription/paypal/deposit/notify', [SubPaypalController::class,'notify'])->name('subscription.paypal.notify');
      Route::get('/subscription/paypal/deposit/cancle', [SubPaypalController::class,'cancle'])->name('subscription.paypal.cancle');

      Route::post('/subscription/instamojo-submit',[SubInstamojoController::class,'store'])->name('subscription.instamojo.submit');
      Route::get('/subscription/instamojo-notify',[SubInstamojoController::class,'notify'])->name('subscription.instamojo.notify');

      Route::post('/subscription/paytm-submit', [SubPaytmController::class,'store'])->name('subscription.paytm.submit');
      Route::post('/subscription/paytm-callback', [SubPaytmController::class,'paytmCallback'])->name('subscription.paytm.notify');

      Route::post('/subscription/razorpay-submit', [SubRazorpayController::class,'store'])->name('subscription.razorpay.submit');
      Route::post('/subscription/razorpay-notify', [SubRazorpayController::class,'notify'])->name('subscription.razorpay.notify');

      Route::post('/subscription/molly-submit', [SubMollieController::class,'store'])->name('subscription.molly.submit');
      Route::get('/subscription/molly-notify', [SubMollieController::class,'notify'])->name('subscription.molly.notify');

      Route::post('/subscription/flutter/submit', [SubFlutterwaveController::class,'store'])->name('subscription.flutter.submit');
      Route::post('/subscription/flutter/notify', [SubFlutterwaveController::class,'notify'])->name('subscription.flutter.notify');

      Route::post('/subscription/authorize-submit', [SubAuthorizeController::class,'store'])->name('subscription.authorize.submit');

      Route::get('/referrals',[ReferralController::class,'referred'])->name('user.referral.index');
      Route::post('/invite-user',[ReferralController::class,'invite_send'])->name('user.referral.invite-user');

      Route::get('/pricingplan/{id}', [SupervisorController::class, 'index'])->name('user-pricingplan');
      Route::get('/pricingplan/edit/{id}', [SupervisorController::class, 'edit'])->name('user-pricingplan-edit');
      Route::get('/pricingplan/create/{id}/{charge_id}', [SupervisorController::class, 'create'])->name('user-pricingplan-create');
      Route::get('/pricingplan/datatables/{id}', [SupervisorController::class, 'datatables'])->name('user-pricingplan-datatables');
      Route::post('/pricingplan/updatecharge/{id}', [SupervisorController::class, 'updateCharge'])->name('user-pricingplan-update-charge');
      Route::post('/pricingplan/createcharge', [SupervisorController::class, 'createCharge'])->name('user-pricingplan-create-charge');
      Route::get('/manager/create', [SupervisorController::class, 'createmanager'])->name('user.manager.create');
      Route::post('/manager/create', [SupervisorController::class, 'storemanager'])->name('user.manager.store');
      Route::get('/manager/delete/{id}', [SupervisorController::class, 'deletemanager'])->name('user.manager.delete');

    //   Route::get('/own/money',[OwnTransferContoller::class, 'index'])->name('user.ownaccounttransfer.index');
      Route::get('/own', [OwnTransferController::class, 'index'])->name('ownaccounttransfer-index');

      Route::post('own',[OwnTransferController::class, 'transfer'])->name('user.ownaccounttransfer.transfer');



      Route::get('/affilate/code', [UserController::class,'affilate_code'])->name('user-affilate-code');


      Route::get('/notf/show', 'User\NotificationController@user_notf_show')->name('customer-notf-show');
      Route::get('/notf/count','User\NotificationController@user_notf_count')->name('customer-notf-count');
      Route::get('/notf/clear','User\NotificationController@user_notf_clear')->name('customer-notf-clear');

      Route::get('admin/messages', [MessageController::class,'adminmessages'])->name('user.message.index');
      Route::get('admin/message/{id}', [MessageController::class,'adminmessage'])->name('user.message.show');
      Route::post('admin/message/post', [MessageController::class,'adminpostmessage'])->name('user.message.store');
      Route::get('admin/message/{id}/delete', [MessageController::class,'adminmessagedelete'])->name('user.message.delete1');
      Route::post('admin/user/send/message', [MessageController::class,'adminusercontact'])->name('user.send.message');
      Route::get('admin/message/load/{id}', [MessageController::class,'messageload'])->name('user.message.load');


      Route::get('/change-password', [UserController::class,'changePasswordForm'])->name('user.change.password.form');
      Route::post('/change-password', [UserController::class,'changePassword'])->name('user.change.password');
    });


    Route::get('/logout', [UserLoginController::class,'logout'])->name('user.logout');

  });
