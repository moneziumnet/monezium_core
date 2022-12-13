<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Admin\DpsController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FdrController;
use App\Http\Controllers\Admin\ICOController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\FontController;
use App\Http\Controllers\Admin\LoanController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BonusController;
use App\Http\Controllers\Admin\EmailController;

use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\CounterController;
use App\Http\Controllers\Admin\DpsPlanController;
use App\Http\Controllers\Admin\FdrPlanController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\SeoToolController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SitemapController;
use App\Http\Controllers\Admin\BankPlanController;
use App\Http\Controllers\Admin\ContactsController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\CryptoCurrencyController;
use App\Http\Controllers\Admin\CryptoDepositController;
use App\Http\Controllers\Admin\CryptoWithdrawController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\LoanPlanController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentsController;
use App\Http\Controllers\Admin\KycManageController;

use App\Http\Controllers\Admin\SubInsBankController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Admin\WithdrawalController;
use App\Http\Controllers\Admin\InstitutionController;
use App\Http\Controllers\Admin\PageSettingController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\BlogCategoryController;

use App\Http\Controllers\Admin\ManageChargeController;

use App\Http\Controllers\Admin\ManageEscrowController;
use App\Http\Controllers\Admin\RequestMoneyController;
use App\Http\Controllers\Admin\AdminLanguageController;

use App\Http\Controllers\Admin\RequestDomainController;
use App\Http\Controllers\Admin\SocialSettingController;
use App\Http\Controllers\Admin\AccountProcessController;
use App\Http\Controllers\Admin\GeneralSettingController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\WithdrawMethodController;
use App\Http\Controllers\Admin\OwnBankTransferController;
use App\Http\Controllers\Admin\WireTransferBankController;
use App\Http\Controllers\Admin\OtherBankTransferController;
use App\Http\Controllers\Admin\DepositController as AppDepositController;
use App\Http\Controllers\Admin\ReferralController as AdminReferralController;
use App\Http\Controllers\Admin\DepositBankController as AppDepositBankController;
use App\Http\Controllers\Admin\WireTransferController as AdminWireTransferController;
use App\Http\Controllers\Deposit\RailsBankController;
use App\Http\Controllers\Deposit\OpenPaydController;
use App\Http\Controllers\User\UserClearJunctionController;
use App\Http\Controllers\Admin\ContractManageController;
use App\Http\Controllers\Admin\SystemAccountController;
use App\Http\Controllers\Admin\MerchantShopController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Deposit\SwanController;


Route::prefix('admin')->group(function () {

  //-----------------------------Clear Cache--------------------
  Route::get('/cache/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    return redirect()->route('admin.dashboard')->with('cache', 'System Cache Has Been Removed.');
  })->name('admin.cache.clear');
  //-----------------------------Clear cache end----------------

  Route::get('/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
  Route::post('/login', [LoginController::class, 'login'])->name('admin.login.submit');
  Route::get('/forgot', [LoginController::class, 'showForgotForm'])->name('admin.forgot');
  Route::post('/forgot-submit', [LoginController::class, 'forgot'])->name('admin.forgot.submit');
  Route::get('/change-password/{token}', [LoginController::class, 'showChangePassForm'])->name('admin.change.token');
  Route::post('/change-password', [LoginController::class, 'changepass'])->name('admin.change.password');
  Route::get('/logout', [LoginController::class, 'logout'])->name('admin.logout');

  Route::get('/profile', [DashboardController::class, 'profile'])->name('admin.profile');
  Route::post('/profile/update', [DashboardController::class, 'profileupdate'])->name('admin.profile.update');
  Route::post('/profile/update/contact', [DashboardController::class, 'profileupdatecontact'])->name('admin.profile.update-contact');
  Route::post('/profile/moduleupdate', [DashboardController::class, 'moduleupdate'])->name('admin.profile.moduleupdate');

  Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
  Route::get('/password', [DashboardController::class, 'passwordreset'])->name('admin.password');
  Route::post('/password/update', [DashboardController::class, 'changepass'])->name('admin.password.update');

  Route::get('/request-domain/datatables', [RequestDomainController::class, 'datatables'])->name('admin.requestdomain.datatables');
  Route::get('/request-domain/create', [RequestDomainController::class, 'create'])->name('admin.requestdomain.create');
  Route::post('/request-domain/create', [RequestDomainController::class, 'store'])->name('admin.requestdomain.store');
  Route::get('/request-domain/{id}/edit', [RequestDomainController::class, 'edit'])->name('admin.requestdomain.edit');
  Route::post('/request-domain/{id}/update', [RequestDomainController::class, 'data_update'])->name('admin.requestdomain.update');
  Route::get('/request-domain/{id}/delete', [RequestDomainController::class, 'destroy'])->name('admin.requestdomain.delete');
  Route::post('request-domain/update', [RequestDomainController::class, 'update'])->name('admin.requestdomain.user.update');

  Route::get('/request-domain', [RequestDomainController::class, 'index'])->name('admin.requestdomain.index');
  Route::get('/request-domain/approve/{id}', [RequestDomainController::class, 'approveStatus'])->name('admin.requestdomain.approve.status');
  Route::get('/request-domain/disapprove/{id}', [RequestDomainController::class, 'disapproveStatus'])->name('admin.requestdomain.disapprove.status');
  Route::post('/request-domain/disapprove-status-update/{id}', [RequestDomainController::class, 'updateStatus'])->name('admin.status.update');


  Route::group(['middleware' => 'permissions:Menu Builder'], function () {
    Route::get('/menu-builder', [GeneralSettingController::class, 'menubuilder'])->name('admin.gs.menubuilder');
  });


  Route::group(['middleware' => 'permissions:Sub Institutions management'], function () {
    Route::get('/institution/datatables', [InstitutionController::class, 'datatables'])->name('admin.institution.datatables');
    Route::get('/institution', [InstitutionController::class, 'index'])->name('admin.institution.index');
    Route::get('/institution/create', [InstitutionController::class, 'create'])->name('admin.institution.create');
    Route::post('/institution/create', [InstitutionController::class, 'store'])->name('admin.institution.store');
    Route::get('/institution/edit/{id}', [InstitutionController::class, 'edit'])->name('admin.institution.edit');
    Route::get('/institution/contact/datatables/{id}', [InstitutionController::class, 'contactsDatatables'])->name('admin.institution.contactsdatatables');
    Route::post('/institution/create-contact/{id}', [InstitutionController::class, 'createContact'])->name('admin.institution.create-contact');
    Route::get('/institution/document/datatables/{id}', [InstitutionController::class, 'documentsDatatables'])->name('admin.institution.documentsdatatables');
    Route::post('/institution/add-document/{id}', [InstitutionController::class, 'createDocument'])->name('admin.institution.add-document');
    Route::get('/institution/block/{id1}/{id2}', [InstitutionController::class, 'block'])->name('admin-staff-block');
    Route::post('/institution/update/{id}', [InstitutionController::class, 'update'])->name('admin.institution.update');
    Route::post('/institution/moduleupdate/{id}', [InstitutionController::class, 'moduleupdate'])->name('admin.institution.moduleupdate');
    Route::get('/institution/delete/{id}', [InstitutionController::class, 'destroy'])->name('admin.institution.delete');
    // for profile of Institution
    Route::get('/institution/{id}/profile', [InstitutionController::class, 'profile'])->name('admin.institution.profile');
    Route::get('/institution/{id}/contacts', [InstitutionController::class, 'contacts'])->name('admin.institution.contacts');
    Route::get('/institution/{id}/contacts/create', [InstitutionController::class, 'createContacts'])->name('admin.institution.contacts.create');

    Route::get('/institution/{id}/modules', [InstitutionController::class, 'modules'])->name('admin.institution.modules');
    Route::get('/institution/{id}/documents', [InstitutionController::class, 'documents'])->name('admin.institution.documents');

    // for Sub Institution
    Route::get('/subinstitution', [InstitutionController::class, 'indexSub'])->name('admin.subinstitution.index');
    Route::get('/subinstitution/create', [InstitutionController::class, 'createSub'])->name('admin.institution.createsub');
    Route::get('/subinstitution/datatables', [InstitutionController::class, 'subDatatables'])->name('admin.subinstitution.datatables');
    Route::get('/subinstitution/{id}/profile', [InstitutionController::class, 'subProfile'])->name('admin.subinstitution.profile');
    Route::get('/subinstitution/{id}/branches', [InstitutionController::class, 'branches'])->name('admin.subinstitution.branches');
    Route::get('/subinstitution/{id}/banks', [InstitutionController::class, 'banks'])->name('admin.subinstitution.banks');
    Route::get('/subinstitution/{id}/paymentgateways', [InstitutionController::class, 'paymentgateways'])->name('admin.subinstitution.paymentgateways');

    Route::get('/branch/datatables', [BranchController::class, 'datatables'])->name('admin.branch.datatables');
    Route::get('/branch', [BranchController::class, 'index'])->name('admin.branch.index');
    Route::get('/branch/create', [BranchController::class, 'create'])->name('admin.branch.create');
    Route::post('/branch/create', [BranchController::class, 'store'])->name('admin.branch.store');
    Route::get('/branch/edit/{id}', [BranchController::class, 'edit'])->name('admin.branch.edit');
    Route::post('/branch/update/{id}', [BranchController::class, 'update'])->name('admin.branch.update');
    Route::get('/branch/delete/{id}', [BranchController::class, 'destroy'])->name('admin.branch.delete');

    Route::post('/general-settings/update/all', [GeneralSettingController::class, 'generalupdate'])->name('admin.gs.update');
    Route::get('/paymentgateway/datatables', [PaymentGatewayController::class, 'datatables'])->name('admin.payment.datatables'); //JSON REQUEST
    Route::get('/paymentgateway', [PaymentGatewayController::class, 'index'])->name('admin.payment.index');
    Route::get('/paymentgateway/create', [PaymentGatewayController::class, 'create'])->name('admin.payment.create');
    Route::post('/paymentgateway/create', [PaymentGatewayController::class, 'store'])->name('admin.payment.store');
    Route::get('/paymentgateway/edit/{id}', [PaymentGatewayController::class, 'edit'])->name('admin.payment.edit');
    Route::post('/paymentgateway/update/{id}', [PaymentGatewayController::class, 'update'])->name('admin.payment.update');
    Route::get('/paymentgateway/delete/{id}', [PaymentGatewayController::class, 'destroy'])->name('admin.payment.delete');
    Route::get('/paymentgateway/status/{id1}/{id2}', [PaymentGatewayController::class, 'status'])->name('admin.payment.status');

    Route::get('/banks/datatables', [SubInsBankController::class, 'datatables'])->name('admin.subinstitution.banks.datatables');
    Route::get('/banks', [SubInsBankController::class, 'index'])->name('admin.subinstitution.banks.index');
    Route::get('/banks/create', [SubInsBankController::class, 'create'])->name('admin.subinstitution.banks.create');
    Route::post('/banks/store', [SubInsBankController::class, 'store'])->name('admin.subinstitution.banks.store');
    Route::get('/banks/edit/{id}', [SubInsBankController::class, 'edit'])->name('admin.subinstitution.banks.edit');
    Route::get('/banks/account/{id}', [SubInsBankController::class, 'account'])->name('admin.subinstitution.banks.account');
    Route::post('/banks/account/create', [OpenPaydController::class, 'master_store'])->name('admin.subinstitution.banks.account.create');
    Route::post('/banks/account/railbank/create', [RailsBankController::class, 'master_store'])->name('admin.subinstitution.banks.account.railsbank.create');


    Route::post('/banks/update/{id}', [SubInsBankController::class, 'update'])->name('admin.subinstitution.banks.update');
    Route::get('/banks/delete/{id}', [SubInsBankController::class, 'destroy'])->name('admin.subinstitution.banks.delete');
    Route::get('/banks/{id1}/status/{status}', [SubInsBankController::class, 'status'])->name('admin.subinstitution.banks.status');

    Route::get('/contacts/datatables', [ContactsController::class, 'datatables'])->name('admin.contacts.datatables');
    Route::get('/contacts/contact-create', [ContactsController::class, 'create'])->name('admin.contact.contact-create');
    Route::get('/contacts/edit/{id}', [ContactsController::class, 'edit'])->name('admin.contact.contact-edit');
    Route::get('/contacts/delete/{id}', [ContactsController::class, 'destroy'])->name('admin.contact.contact-delete');

    Route::get('/documents/datatables', [DocumentsController::class, 'datatables'])->name('admin.documents.datatables');
    Route::post('/documents/create-document', [DocumentsController::class, 'store'])->name('admin.document.add-document');
    Route::get('/documents/download/{id}', [DocumentsController::class, 'getDownload'])->name('admin.documents.download');
    Route::get('/documents/delete/{id}', [DocumentsController::class, 'destroy'])->name('admin.documents.document-delete');
  });

  Route::group(['middleware' => 'permissions:Contract Management'], function () {
    Route::get('/contract/management/{id}', [ContractManageController::class, 'index'])->name('admin.contract.management');
    Route::get('/contract/datatables/{id}', [ContractManageController::class, 'datatables'])->name('admin.contract.datatables');
    Route::get('/contract/view/{id}', [ContractManageController::class, 'view'])->name('admin.contract.view');
    Route::get('/contract/aoa/{id}', [ContractManageController::class, 'aoa_index'])->name('admin.aoa.index');
    Route::get('/contract/aoa/{id}/datatables', [ContractManageController::class, 'aoa_datatables'])->name('admin.aoa.datatables');
    Route::get('/contract/aoa/view/{id}', [ContractManageController::class, 'aoa_view'])->name('admin.aoa.view');

  });

  Route::group(['middleware' => 'permissions:Manage Customers'], function () {
    Route::get('/users/bonus', [BonusController::class, 'index'])->name('admin.user.bonus');
    Route::post('/users/edit/', [BonusController::class, 'update'])->name('admin.bonus.update');

    Route::get('/users/datatables', [UserController::class, 'datatables'])->name('admin-user-datatables'); //JSON REQUEST
    Route::get('/users', [UserController::class, 'index'])->name('admin.user.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('admin.user.create');
    Route::post('/users/create', [UserController::class, 'store'])->name('admin.user.store');
    Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('admin-user-edit');
    Route::post('/users/edit/{id}', [UserController::class, 'update'])->name('admin-user-update');
    Route::get('/users/delete/{id}', [UserController::class, 'destroy'])->name('admin-user-delete');
    Route::get('/users/login/{id}', [UserController::class, 'login'])->name('admin-user-login');
    Route::get('/user/{id}/profile', [UserController::class, 'profileInfo'])->name('admin-user-profile');
    Route::get('/user/document/create/{user_id}', [UserController::class,'createfile'])->name('admin-user.createfile');
    Route::post('/user/document/create/{user_id}', [UserController::class,'storefile'])->name('admin-user.createfile');
    Route::get('/user/document/download/{id}', [UserController::class,'fileDownload'])->name('admin-user.download');
    Route::get('/user/document/fileview/{id}', [UserController::class,'fileView'])->name('admin-user.view-document');
    Route::get('/user/document/delete/{id}', [UserController::class,'fileDestroy'])->name('admin-user.document-delete');
    Route::get('/user/transactions/datatables/{id}', [UserController::class, 'trandatatables'])->name('admin-user.transactions-datatables');
    Route::get('/user/transaction/details/{id}', [UserController::class,'trxDetails'])->name('admin-user.trxDetails');
    Route::get('/user/transactions/edit/{tid}', [UserController::class, 'transctionEdit'])->name('admin-user.transaction-edit');
    Route::get('/user/transactions/delete/{tid}', [UserController::class, 'transctionDelete'])->name('admin-user.transaction-delete');
    Route::post('/user/transactions/edit/{tid}', [UserController::class, 'transctionUpdate'])->name('admin-user.transaction-edit');
    Route::get('/user/transaction/pdf/{id}', [UserController::class,'transactionPDF'])->name('admin-user.transaction-pdf');
    Route::get('/user/transaction/export/{id}', [UserController::class,'transactionExport'])->name('admin-user.transaction-export');

    Route::get('/user/{id}/accounts', [UserController::class, 'profileAccounts'])->name('admin-user-accounts');
    Route::get('/user/{id}/accounts/wallets/{wallet_type}/{currency_id}',[UserController::class, 'profilewallets'])->name('admin-user-wallets');
    Route::get('/user/{id}/accounts/wallet/create/{wallet_type}/{currency_id}',[UserController::class, 'profilewalletcreate'])->name('admin-user-wallet-create');
    Route::get('/user/{id}/documents', [UserController::class, 'profileDocuments'])->name('admin-user-documents');
    Route::get('/user/{id}/settings', [UserController::class, 'profileSettings'])->name('admin-user-settings');
    Route::get('/user/{id}/pricingplan', [UserController::class, 'profilePricingplan'])->name('admin-user-pricingplan');
    Route::get('/user/{id}/pricingplan_supervisor', [UserController::class, 'profilePricingplan_supervisor'])->name('admin-user-pricingplan-supervisor');
    Route::get('/user/{id}/pricingplan_manager', [UserController::class, 'profilePricingplan_manager'])->name('admin-user-pricingplan-manager');
    Route::get('/user/pricingplan/edit/{id}', [UserController::class, 'profilePricingplanedit'])->name('admin-user-pricingplan-edit');
    Route::get('/user/pricingplan/create/{id}/{charge_id}', [UserController::class, 'profilePricingplancreate'])->name('admin-user-pricingplan-create');
    Route::get('/user/pricingplan/supervisor/create/{id}/{charge_id}', [UserController::class, 'profilePricingplanSupervisorcreate'])->name('admin-user-pricingplan-supervisor-create');
    Route::get('/user/pricingplancreate/{id}', [UserController::class, 'profilePricingplanglobalcreate'])->name('admin-user-pricingplan-global-create');
    Route::get('/user/pricingplan/datatables/{id}', [UserController::class, 'profilePricingplandatatables'])->name('admin-user-pricingplan-datatables');
    Route::get('/user/pricingplan/supervisor/datatables/{id}', [UserController::class, 'profilePricingplanSupervisordatatables'])->name('admin-user-pricingplan-supervisor-datatables');
    Route::post('/user/{id}/admin-user-upgrade-plan', [UserController::class, 'upgradePlan'])->name('admin-user-upgrade-plan');
    Route::get('/user/{id}/transactions', [UserController::class, 'profileTransctions'])->name('admin-user-transactions');
    Route::get('/user/{id}/banks', [UserController::class, 'profileBanks'])->name('admin-user-banks');
    Route::get('/user/{id}/bankaccount', [UserController::class, 'profileBankAccount'])->name('admin-user-bank-account');
    Route::post('/user/bank/nogateway', [UserController::class, 'storeBankAccount'])->name('admin.user.bank.nogateway');
    Route::POST('/user/bank/railsbank',[RailsBankController::class,'store'])->name('admin.user.bank.railsbank');
    Route::POST('/user/bank/openpayd',[OpenPaydController::class,'store'])->name('admin.user.bank.openpayd');
    Route::POST('/user/bank/clearjunction',[UserClearJunctionController::class,'AllocateIbanCreate'])->name('admin.user.bank.clearjunction');
    Route::POST('/user/bank/swan',[SwanController::class,'store'])->name('admin.user.bank.swan');
    Route::POST('/user/bank/gateway',[UserController::class,'gateway'])->name('admin-user-bank-gateway');
    Route::POST('/user/bank/updateinfo',[UserController::class,'updateinfo'])->name('admin-user-bank-updateinfo');
    Route::get('/user/{id}/modules', [UserController::class, 'profileModules'])->name('admin-user-modules');
    Route::post('/users/{id}/changepassword', [UserController::class, 'changePassword'])->name('admin-user-changepassword');
    Route::post('/users/{id}/updatemodules', [UserController::class, 'updateModules'])->name('admin-user-updatemodules');

    Route::get('/wallet/{user_id}/{wallet_id}/transactions', [UserController::class, 'walletTransctions'])->name('admin-wallet-transactions');
    Route::get('/wallet/transactions/datatables/{id}', [UserController::class, 'walletTrandatatables'])->name('admin-wallet.transactions-datatables');
    Route::get('/wallet/{user_id}/{wallet_id}/internal', [UserController::class, 'internal'])->name('admin-wallet-internal');
    Route::post('/wallet/{user_id}/{wallet_id}/internal/send', [UserController::class, 'internal_send'])->name('admin-wallet-internal-send');
    Route::get('/wallet/{user_id}/{wallet_id}/external', [UserController::class, 'external'])->name('admin-wallet-external');
    Route::post('/wallet/{user_id}/{wallet_id}/external/send', [UserController::class, 'external_send'])->name('admin-wallet-external-send');
    Route::get('/wallet/{user_id}/{wallet_id}/between', [UserController::class, 'between'])->name('admin-wallet-between');
    Route::post('/wallet/{user_id}/{wallet_id}/between/send', [UserController::class, 'between_send'])->name('admin-wallet-between-send');

    Route::get('/user/accounts/fee/{id}', [UserController::class, 'profileAccountFee'])->name('admin-user-accounts-fee');
    Route::post('/user/cal/manual/fee/', [UserController::class, 'calmanualfee'])->name('admin.cal.manual.charge');
    Route::post('/user/accounts/deposit', [UserController::class, 'profileAccountDeposit'])->name('admin-user-accounts-deposit');
    Route::get('/user/accounts/deposit/form', [UserController::class, 'profileAccountDepositForm'])->name('admin-user-accounts-deposit-form');
    Route::get('/user/accounts/crypto/deposit/form/{id}', [UserController::class, 'profileAccountCryptoDepositForm'])->name('admin-user-accounts-crypto-deposit-form');

    Route::get('/users/ban/{id1}/{id2}', [UserController::class, 'ban'])->name('admin-user-ban');
    Route::get('/users/verify/{id1}/{id2}', [UserController::class, 'verify'])->name('admin-user-verify');
    Route::get('/user/default/image', [UserController::class, 'image'])->name('admin-user-image');
    Route::get('/users/deposit/{id}', [UserController::class, 'deposit'])->name('admin-user-deposit');
    Route::post('/user/deposit/{id}', [UserController::class, 'depositUpdate'])->name('admin-user-deposit-update');
    Route::post('/user/balance/add/deduct', [UserController::class, 'adddeduct'])->name('admin.user.balance.add.deduct');

    Route::get('/bank-plan/datatables', [BankPlanController::class, 'datatables'])->name('admin.bank.plan.datatables');
    Route::get('/bank-plan/detaildatatables/{id}', [BankPlanController::class, 'detaildatatables'])->name('admin.bank.plan.detail.datatables');
    Route::get('/bank-plans', [BankPlanController::class, 'index'])->name('admin.bank.plan.index');
    Route::get('/bank-plan/create', [BankPlanController::class, 'create'])->name('admin.bank.plan.create');
    Route::get('/bank-plan/edit/{id}', [BankPlanController::class, 'edit'])->name('admin.bank.plan.edit');
    Route::get('/bank-plan/delete/{id}', [BankPlanController::class, 'destroy'])->name('admin.bank.plan.delete');
    Route::post('/bank-plan/store', [BankPlanController::class, 'store'])->name('admin.bank.plan.store');
    Route::post('/bank-plan/update/{id}', [BankPlanController::class, 'update'])->name('admin.bank.plan.update');
    Route::post('/bank-plan/detail/update/{id}', [BankPlanController::class, 'plandetailupdate'])->name('admin.bank.plan.detail.update');
    Route::get('/bank-plan/detail/{id}', [BankPlanController::class, 'plandetailget'])->name('admin.bank.plan.detail.get');

    Route::get('/plan/datatables', [PlanController::class, 'datatables'])->name('admin.plan.datatables');
    Route::get('/plans', [PlanController::class, 'index'])->name('admin.plan.index');
    Route::get('/plan/create', [PlanController::class, 'create'])->name('admin.plan.create');
    Route::get('/plan/edit/{id}', [PlanController::class, 'edit'])->name('admin.plan.edit');
    Route::get('/plan/delete/{id}', [PlanController::class, 'destroy'])->name('admin.plan.delete');
    Route::post('/plan/store', [PlanController::class, 'store'])->name('admin.plan.store');
    Route::post('/plan/update/{id}', [PlanController::class, 'update'])->name('admin.plan.update');

    Route::get('/merchant/shop/{id}', [MerchantShopController::class, 'index'])->name('admin.merchant.shop.index');
    Route::get('/merchant/shop/datatables/{id}', [MerchantShopController::class, 'datatables'])->name('admin.merchant.shop.datatables');
    Route::get('/merchant/shop/status/{id1}/{id2}', [MerchantShopController::class, 'status'])->name('admin.merchant.shop.status');

    Route::post('/username-by-email', [UserController::class,'username_by_email'])->name('admin.username.email');
    Route::post('/username-by-phone', [UserController::class,'username_by_phone'])->name('admin.username.phone');

  });

  Route::group(['middleware' => 'permissions:Loan Management'], function () {
    Route::get('/loan-plans/datatables', [LoanPlanController::class, 'datatables'])->name('admin.loan.plan.datatables');
    Route::get('/loan-plans', [LoanPlanController::class, 'index'])->name('admin.loan.plan.index');
    Route::get('/loan-plans/create', [LoanPlanController::class, 'create'])->name('admin.loan.plan.create');
    Route::post('/loan-plans/store', [LoanPlanController::class, 'store'])->name('admin.loan.plan.store');
    Route::get('/loan-plans/edit/{id}', [LoanPlanController::class, 'edit'])->name('admin.loan.plan.edit');
    Route::post('/loan-plans/update/{id}', [LoanPlanController::class, 'update'])->name('admin.loan.plan.update');
    Route::get('/loan-plans/delete/{id}', [LoanPlanController::class, 'destroy'])->name('admin.loan.plan.delete');
    Route::get('/loan-plans/{id1}/status/{status}', [LoanPlanController::class, 'status'])->name('admin.loan.plan.status');

    Route::get('/loan-installment/cron', [LoanController::class, 'installmentCheck'])->name('admin.loan.installment.cron');
    Route::get('/loan/datatables/{status}', [LoanController::class, 'datatables'])->name('admin.loan.datatables');
    Route::get('/loan', [LoanController::class, 'index'])->name('admin.loan.index');
    Route::get('/completed-loan', [LoanController::class, 'completed'])->name('admin.loan.completed');
    Route::get('/running-loan', [LoanController::class, 'running'])->name('admin.loan.running');
    Route::get('/pending-loan', [LoanController::class, 'pending'])->name('admin.loan.pending');
    Route::get('/rejected-loan', [LoanController::class, 'rejected'])->name('admin.loan.rejected');
    Route::get('/loan/status/{id1}/{id2}', [LoanController::class, 'status'])->name('admin.loan.status');
    Route::get('/loan/show/{id}', [LoanController::class, 'show'])->name('admin.loan.show');
    Route::get('/loan-log/show/{id}', [LoanController::class, 'logShow'])->name('admin.loan.log.show');
  });

  Route::group(['middleware' => 'permissions:DPS Management'], function () {
    Route::get('/dps-plans/datatables', [DpsPlanController::class, 'datatables'])->name('admin.dps.plan.datatables');
    Route::get('/dps-plans', [DpsPlanController::class, 'index'])->name('admin.dps.plan.index');
    Route::get('/dps-plans/create', [DpsPlanController::class, 'create'])->name('admin.dps.plan.create');
    Route::post('/dps-plans/store', [DpsPlanController::class, 'store'])->name('admin.dps.plan.store');
    Route::get('/dps-plans/edit/{id}', [DpsPlanController::class, 'edit'])->name('admin.dps.plan.edit');
    Route::post('/dps-plans/update/{id}', [DpsPlanController::class, 'update'])->name('admin.dps.plan.update');
    Route::get('/dps-plans/delete/{id}', [DpsPlanController::class, 'destroy'])->name('admin.dps.plan.delete');
    Route::get('/dps-plans/{id1}/status/{status}', [DpsPlanController::class, 'status'])->name('admin.dps.plan.status');

    Route::get('/dps-installment/cron', [DpsController::class, 'installmentCheck'])->name('admin.dps.installment.cron');
    Route::get('/dps/datatables/{status}', [DpsController::class, 'datatables'])->name('admin.dps.datatables');
    Route::get('/dps', [DpsController::class, 'index'])->name('admin.dps.index');
    Route::get('/running-dps', [DpsController::class, 'running'])->name('admin.dps.running');
    Route::get('/matured-dps', [DpsController::class, 'matured'])->name('admin.dps.matured');
    Route::get('/dps-log/show/{id}', [DpsController::class, 'logShow'])->name('admin.dps.log.show');
  });

  Route::group(['middleware' => 'permissions:FDR Management'], function () {
    Route::get('/fdr-plans/datatables', [FdrPlanController::class, 'datatables'])->name('admin.fdr.plan.datatables');
    Route::get('/fdr-plans', [FdrPlanController::class, 'index'])->name('admin.fdr.plan.index');
    Route::get('/fdr-plans/create', [FdrPlanController::class, 'create'])->name('admin.fdr.plan.create');
    Route::post('/fdr-plans/store', [FdrPlanController::class, 'store'])->name('admin.fdr.plan.store');
    Route::get('/fdr-plans/edit/{id}', [FdrPlanController::class, 'edit'])->name('admin.fdr.plan.edit');
    Route::post('/fdr-plans/update/{id}', [FdrPlanController::class, 'update'])->name('admin.fdr.plan.update');
    Route::get('/fdr-plans/delete/{id}', [FdrPlanController::class, 'destroy'])->name('admin.fdr.plan.delete');
    Route::get('/fdr-plans/{id1}/status/{status}', [FdrPlanController::class, 'status'])->name('admin.fdr.plan.status');

    Route::get('/fdr-installment/cron', [FdrController::class, 'nextProfitCheck'])->name('admin.fdr.installment.cron');
    Route::get('/fdr/datatables/{status}', [FdrController::class, 'datatables'])->name('admin.fdr.datatables');
    Route::get('/fdr', [FdrController::class, 'index'])->name('admin.fdr.index');
    Route::get('/running-fdr', [FdrController::class, 'running'])->name('admin.fdr.running');
    Route::get('/closed-fdr', [FdrController::class, 'closed'])->name('admin.fdr.closed');
  });

  Route::group(['middleware' => 'permissions:ICO Management'], function () {
    Route::get('/ico', [ICOController::class, 'index'])->name('admin.ico.index');
    Route::get('/ico/details/{id}', [ICOController::class, 'details'])->name('admin.ico.details');
    Route::get('/ico/datatables', [ICOController::class, 'datatables'])->name('admin.ico.datatables');
    Route::get('/ico/status/{id}/{status}', [ICOController::class, 'status'])->name('admin.ico.status');
  });



  Route::group(['middleware' => 'permissions:Withdraw'], function () {
    Route::get('withdraw/method/datatables', [WithdrawMethodController::class, 'datatables'])->name('admin.withdraw.method.datatables'); //->middleware('permission:withdraw method');
    // Route::get('withdraw/method', [WithdrawMethodController::class, 'index'])->name('admin.withdraw'); //->middleware('permission:withdraw method');
    Route::get('withdraw/method-create', [WithdrawMethodController::class, 'create'])->name('admin.withdraw.create'); //->middleware('permission:withdraw method create');
    Route::post('withdraw/method-create', [WithdrawMethodController::class, 'store']); //->middleware('permission:withdraw method create');
    Route::get('withdraw/method/search', [WithdrawMethodController::class, 'index'])->name('admin.withdraw.search'); //->middleware('permission:withdraw method search');
    Route::get('withdraw/method/edit/{id}', [WithdrawMethodController::class, 'edit'])->name('admin.withdraw.edit'); //->middleware('permission:withdraw method edit');
    Route::post('withdraw/update/{method}', [WithdrawMethodController::class, 'update'])->name('admin.withdraw.update'); //->middleware('permission:withdraw method update');
    Route::get('withdraw/pending', [WithdrawalController::class, 'pending'])->name('admin.withdraw.pending'); //->middleware('permission:pending withdraw');
    Route::get('withdraw/accepted', [WithdrawalController::class, 'accepted'])->name('admin.withdraw.accepted'); //->middleware('permission:accepted withdraw');
    Route::get('withdraw/rejected', [WithdrawalController::class, 'rejected'])->name('admin.withdraw.rejected'); //->middleware('permission:rejected withdraw');
    Route::post('withdraw/accept/{withdraw}', [WithdrawalController::class, 'withdrawAccept'])->name('admin.withdraw.accept'); //->middleware('permission:withdraw accept');
    Route::post('withdraw/reject/{withdraw}', [WithdrawalController::class, 'withdrawReject'])->name('admin.withdraw.reject'); //->middleware('permission:withdraw reject');
  });

  //==================================== Manage Currency ==============================================//

  // manage charges
  Route::group(['middleware' => 'permissions:Manage Charges'], function () {
    Route::get('/manage-charges/{id}', [ManageChargeController::class, 'index'])->name('admin.manage.charge');
    Route::get('/edit-charge/{id}', [ManageChargeController::class, 'editCharge'])->name('admin.edit.charge');
    Route::post('/create-charge', [ManageChargeController::class, 'createCharge'])->name('admin.create.charge');
    Route::post('/update-charge/{id}', [ManageChargeController::class, 'updateCharge'])->name('admin.update.charge');
    Route::get('/database-charge/{id}', [ManageChargeController::class, 'datatables'])->name('admin.charge.plan.datatables');
  });

  //manage escrow
  Route::group(['middleware' => 'permissions:Manage Escrow'], function () {
    Route::get('manage/escrow', [ManageEscrowController::class, 'index'])->name('admin.escrow.manage');
    Route::get('escrow/on-hold', [ManageEscrowController::class, 'onHold'])->name('admin.escrow.onHold');
    Route::get('escrow-disputed', [ManageEscrowController::class, 'disputed'])->name('admin.escrow.disputed');
    Route::get('escrow-details/{id}', [ManageEscrowController::class, 'details'])->name('admin.escrow.details');
    Route::post('escrow-details/{id}', [ManageEscrowController::class, 'disputeStore']);
    Route::get('file-download/{id}',   [ManageEscrowController::class, 'fileDownload'])->name('admin.escrow.file.download');
    Route::post('return-payment',   [ManageEscrowController::class, 'returnPayment'])->name('admin.escrow.return.payment');
    Route::get('escrow-close/{id}', [ManageEscrowController::class, 'close'])->name('admin.escrow.close');
  });

  Route::group(['middleware' => 'permissions:Bank Transfer'], function () {
    Route::get('/own-banks/transfer/datatables', [OwnBankTransferController::class, 'datatables'])->name('admin.own.banks.transfer.datatables');
    Route::get('/own-banks/transfer', [OwnBankTransferController::class, 'index'])->name('admin.own.banks.transfer.index');

    Route::get('/other-banks/transfer/datatables', [OtherBankTransferController::class, 'datatables'])->name('admin.other.banks.transfer.datatables');
    Route::get('/other-banks/transfer/subdatatables', [OtherBankTransferController::class, 'subdatatables'])->name('admin.other.banks.transfer.subdatatables');
    Route::get('/other-banks/transfer', [OtherBankTransferController::class, 'index'])->name('admin.other.banks.transfer.index');
    Route::get('/other-banks/transfer/show/{id}', [OtherBankTransferController::class, 'show'])->name('admin.other.banks.transfer.show');
    Route::get('/other-banks/transfer/details/{id}', [OtherBankTransferController::class, 'details'])->name('admin.other.banks.transfer.details');
    Route::get('/other-banks/transfer/{id1}/status/{status}', [OtherBankTransferController::class, 'status'])->name('admin.other.banks.transfer.status');
  });

  Route::group(['middleware' => 'permissions:Wire Transfer'], function () {
    Route::get('/wire-transfer-banks/datatables', [WireTransferBankController::class, 'datatables'])->name('admin.wire.transfer.banks.datatables');
    Route::get('/wire-transfer-banks', [WireTransferBankController::class, 'index'])->name('admin.wire.transfer.banks.index');
    Route::get('/wire-transfer-banks/create', [WireTransferBankController::class, 'create'])->name('admin.wire.transfer.banks.create');
    Route::post('/wire-transfer-banks/store', [WireTransferBankController::class, 'store'])->name('admin.wire.transfer.banks.store');
    Route::get('/wire-transfer-banks/edit/{id}', [WireTransferBankController::class, 'edit'])->name('admin.wire.transfer.banks.edit');
    Route::post('/wire-transfer-banks/update/{id}', [WireTransferBankController::class, 'update'])->name('admin.wire.transfer.banks.update');
    Route::get('/wire-transfer-banks/delete/{id}', [WireTransferBankController::class, 'destroy'])->name('admin.wire.transfer.banks.delete');

    Route::get('/wire-transfers/datatables', [AdminWireTransferController::class, 'datatables'])->name('admin.wire.transfer.datatables');
    Route::get('wire-transfers', [AdminWireTransferController::class, 'index'])->name('admin.wire.transfer.index');
    Route::get('/wire-transfers/show/{id}', [AdminWireTransferController::class, 'show'])->name('admin.wire.transfer.show');
    Route::get('/wire-transfers/status/{id1}/{id2}', [AdminWireTransferController::class, 'status'])->name('admin.wire.transfer.status');
  });

  Route::group(['middleware' => 'permissions:Request Money'], function () {
    Route::get('/request-money/datatables', [RequestMoneyController::class, 'datatables'])->name('admin.request.datatables');
    Route::get('/request-money', [RequestMoneyController::class, 'index'])->name('admin.request.money');
    Route::get('/request-money/create', [RequestMoneyController::class, 'create'])->name('admin.request.setting.create');
  });

  Route::group(['middleware' => 'permissions:Transactions'], function () {
    Route::get('/transactions/datatables', [TransactionController::class, 'datatables'])->name('admin.transactions.datatables');
    Route::get('/transactions', [TransactionController::class, 'index'])->name('admin.transactions.index');
  });

  Route::group(['middleware' => 'permissions:Deposits'], function () {
    Route::get('/deposits/datatables', [AppDepositController::class, 'datatables'])->name('admin.deposits.datatables');
    Route::get('/deposits', [AppDepositController::class, 'index'])->name('admin.deposits.index');
    Route::get('/deposits/status/{id1}/{id2}', [AppDepositController::class, 'status'])->name('admin.deposits.status');

    Route::get('/deposits/bank/datatables', [AppDepositBankController::class, 'datatables'])->name('admin.deposits.bank.datatables');
    Route::get('/deposits/bank', [AppDepositBankController::class, 'index'])->name('admin.deposits.bank.index');
    Route::get('/deposits/bank/status/{id1}/{id2}', [AppDepositBankController::class, 'status'])->name('admin.deposits.bank.status');
  });

  Route::group(['middleware' => 'permissions:Crowdfunding'], function () {
    Route::get('/campaign/datatables', [CampaignController::class, 'datatables'])->name('admin.campaign.datatables');
    Route::get('/campaign', [CampaignController::class, 'index'])->name('admin.campaign.index');
    Route::get('/campaign/status/{id1}/{id2}', [CampaignController::class, 'status'])->name('admin.campaign.status');
    Route::get('/campaign/delete/{id}', [CampaignController::class, 'destroy'])->name('admin.campaign.delete');
    Route::get('/campaign/donation/datatables', [CampaignController::class, 'donation_datatables'])->name('admin.donation.datatables');
    Route::get('/campaign/donation', [CampaignController::class, 'donation_index'])->name('admin.donation.index');
    Route::get('/campaign/donation/status/{id1}/{id2}', [CampaignController::class, 'donation_status'])->name('admin.donation.status');
    Route::get('/campaign/donation/delete/{id}', [CampaignController::class, 'donation_destroy'])->name('admin.donation.delete');
});

  Route::group(['middleware' => 'permissions:Manage Blog'], function () {
    Route::get('/blog/datatables', [BlogController::class, 'datatables'])->name('admin.blog.datatables'); //JSON REQUEST
    Route::get('/blog', [BlogController::class, 'index'])->name('admin.blog.index');
    Route::get('/blog/create', [BlogController::class, 'create'])->name('admin.blog.create');
    Route::post('/blog/create', [BlogController::class, 'store'])->name('admin.blog.store');
    Route::get('/blog/edit/{id}', [BlogController::class, 'edit'])->name('admin.blog.edit');
    Route::post('/blog/edit/{id}', [BlogController::class, 'update'])->name('admin.blog.update');
    Route::get('/blog/delete/{id}', [BlogController::class, 'destroy'])->name('admin.blog.delete');

    Route::get('/blog/category/datatables', [BlogCategoryController::class, 'datatables'])->name('admin.cblog.datatables'); //JSON REQUEST
    Route::get('/blog/category', [BlogCategoryController::class, 'index'])->name('admin.cblog.index');
    Route::get('/blog/category/create', [BlogCategoryController::class, 'create'])->name('admin.cblog.create');
    Route::post('/blog/category/create', [BlogCategoryController::class, 'store'])->name('admin.cblog.store');
    Route::get('/blog/category/edit/{id}', [BlogCategoryController::class, 'edit'])->name('admin.cblog.edit');
    Route::post('/blog/category/edit/{id}', [BlogCategoryController::class, 'update'])->name('admin.cblog.update');
    Route::get('/blog/category/delete/{id}', [BlogCategoryController::class, 'destroy'])->name('admin.cblog.delete');
  });
  Route::get('/system-settings', [SystemAccountController::class, 'systemAccounts'])->name('admin.system.accounts');
  Route::get('/system-settings/create/{currency_id}', [SystemAccountController::class, 'create'])->name('admin.system.account.create');
  Route::get('/system-settings/{keyword}', [SystemAccountController::class, 'setting'])->name('admin.system.crypto.api');
  Route::get('/crypto/binance', [SystemAccountController::class, 'binance_setting'])->name('admin.system.crypto.binance.api');
  Route::post('/system-settings/save', [SystemAccountController::class, 'setting_save'])->name('admin.system.crypto.api.save');
  Route::post('/system-settings/depositAddresses', [SystemAccountController::class, 'depositAddresses'])->name('admin.system.crypto.depositaddress');
  Route::post('/system-settings/binance/depositAddresses', [SystemAccountController::class, 'binance_depositAddresses'])->name('admin.system.crypto.binance.depositaddress');
  Route::post('/system-settings/depositMethods', [SystemAccountController::class, 'depositMethods'])->name('admin.system.crypto.depositMethods');
  Route::post('/system-settings/order', [SystemAccountController::class, 'order'])->name('admin.system.crypto.order');
  Route::post('/system-settings/binance/order', [SystemAccountController::class, 'binance_order'])->name('admin.system.crypto.binance.order');
  Route::post('/system-settings/withdraw', [SystemAccountController::class, 'withdraw'])->name('admin.system.crypto.withdraw');
  Route::post('/system-settings/binance/withdraw', [SystemAccountController::class, 'binance_withdraw'])->name('admin.system.crypto.binance.withdraw');

  Route::group(['middleware' => 'permissions:General Setting'], function () {
    Route::get('/general-settings/logo', [GeneralSettingController::class, 'logo'])->name('admin.gs.logo');
    Route::get('/general-settings/favicon', [GeneralSettingController::class, 'fav'])->name('admin.gs.fav');
    Route::get('/general-settings/loader', [GeneralSettingController::class, 'load'])->name('admin.gs.load');
    Route::post('/general-settings/update/all', [GeneralSettingController::class, 'generalupdate'])->name('admin.gs.update');
    Route::get('/general-settings/contents', [GeneralSettingController::class, 'contents'])->name('admin.gs.contents');
    Route::get('/general-settings/user/modules', [GeneralSettingController::class, 'usermodules'])->name('admin.gs.user.modules');
    Route::get('/general-settings/theme', [GeneralSettingController::class, 'theme'])->name('admin.gs.theme');

    Route::get('/general-settings/breadcumb', [GeneralSettingController::class, 'breadcumb'])->name('admin.gs.breadcumb');
    Route::get('/general-settings/status/{field}/{status}', [GeneralSettingController::class, 'status'])->name('admin.gs.status');
    Route::get('/general-settings/footer', [GeneralSettingController::class, 'footer'])->name('admin.gs.footer');
    Route::get('/general-settings/affilate', [GeneralSettingController::class, 'affilate'])->name('admin.gs.affilate');
    Route::get('/general-settings/error-banner', [GeneralSettingController::class, 'errorbanner'])->name('admin.gs.error.banner');
    Route::get('/general-settings/popup', [GeneralSettingController::class, 'popup'])->name('admin.gs.popup');
    Route::get('/general-settings/maintenance', [GeneralSettingController::class, 'maintain'])->name('admin.gs.maintenance');


    Route::get('/twilio-sms-settings', [GeneralSettingController::class, 'twilio'])->name('admin.gs.twilio');
    // Update Nexmo
    Route::get('/nexmo-sms-settings', [GeneralSettingController::class, 'nexmo'])->name('admin.gs.nexmo');
    Route::get('/currency-api-settings', [GeneralSettingController::class, 'currencyapi'])->name('admin.gs.currencyapi');
  });

  Route::group(['middleware' => 'permissions:Home page Setting'], function () {
    //------------ ADMIN FEATURE SECTION ------------
    Route::get('/feature/datatables', [FeatureController::class, 'datatables'])->name('admin.feature.datatables'); //JSON REQUEST
    Route::get('/feature', [FeatureController::class, 'index'])->name('admin.feature.index');
    Route::get('/feature/create', [FeatureController::class, 'create'])->name('admin.feature.create');
    Route::post('/feature/create', [FeatureController::class, 'store'])->name('admin.feature.store');
    Route::get('/feature/edit/{id}', [FeatureController::class, 'edit'])->name('admin.feature.edit');
    Route::post('/feature/update/{id}', [FeatureController::class, 'update'])->name('admin.feature.update');
    Route::get('/feature/delete/{id}', [FeatureController::class, 'destroy'])->name('admin.feature.delete');
    //------------ ADMIN FEATURE SECTION ENDS ------------

    //------------ ADMIN SERVICE SECTION ------------
    Route::get('/service/datatables', [ServiceController::class, 'datatables'])->name('admin.service.datatables'); //JSON REQUEST
    Route::get('/service', [ServiceController::class, 'index'])->name('admin.service.index');
    Route::get('/service/create', [ServiceController::class, 'create'])->name('admin.service.create');
    Route::post('/service/store', [ServiceController::class, 'store'])->name('admin.service.store');
    Route::get('/service/edit/{id}', [ServiceController::class, 'edit'])->name('admin.service.edit');
    Route::post('/service/edit/{id}', [ServiceController::class, 'update'])->name('admin.service.update');
    Route::get('/service/delete/{id}', [ServiceController::class, 'destroy'])->name('admin.service.delete');
    //------------ ADMIN SERVICE SECTION ENDS ------------

    Route::get('/account/process/datatables', [AccountProcessController::class, 'datatables'])->name('admin.account.process.datatables'); //JSON REQUEST
    Route::get('/account/process', [AccountProcessController::class, 'index'])->name('admin.account.process.index');
    Route::get('/account/process/create', [AccountProcessController::class, 'create'])->name('admin.account.process.create');
    Route::post('/account/process/store', [AccountProcessController::class, 'store'])->name('admin.account.process.store');
    Route::get('/account/process/edit/{id}', [AccountProcessController::class, 'edit'])->name('admin.account.process.edit');
    Route::post('/account/process/edit/{id}', [AccountProcessController::class, 'update'])->name('admin.account.process.update');
    Route::get('/account/process/delete/{id}', [AccountProcessController::class, 'destroy'])->name('admin.account.process.delete');

    Route::get('/review/datatables', [ReviewController::class, 'datatables'])->name('admin.review.datatables'); //JSON REQUEST
    Route::get('/review', [ReviewController::class, 'index'])->name('admin.review.index');
    Route::get('/review/create', [ReviewController::class, 'create'])->name('admin.review.create');
    Route::post('/review/store', [ReviewController::class, 'store'])->name('admin.review.store');
    Route::get('/review/edit/{id}', [ReviewController::class, 'edit'])->name('admin.review.edit');
    Route::post('/review/edit/{id}', [ReviewController::class, 'update'])->name('admin.review.update');
    Route::get('/review/delete/{id}', [ReviewController::class, 'destroy'])->name('admin.review.delete');

    Route::get('/counter/datatables', [CounterController::class, 'datatables'])->name('admin.counter.datatables'); //JSON REQUEST
    Route::get('/counter', [CounterController::class, 'index'])->name('admin.counter.index');
    Route::get('/counter/create', [CounterController::class, 'create'])->name('admin.counter.create');
    Route::post('/counter/store', [CounterController::class, 'store'])->name('admin.counter.store');
    Route::get('/counter/edit/{id}', [CounterController::class, 'edit'])->name('admin.counter.edit');
    Route::post('/counter/edit/{id}', [CounterController::class, 'update'])->name('admin.counter.update');
    Route::get('/counter/delete/{id}', [CounterController::class, 'destroy'])->name('admin.counter.delete');


    //------------ ADMIN MENU PAGE SETTINGS SECTION ------------
    Route::get('/page-settings/hero', [PageSettingController::class, 'hero'])->name('admin.ps.hero');
    Route::get('/page-settings/quick-start', [PageSettingController::class, 'quickStart'])->name('admin.ps.quick');
    Route::get('/page-settings/about', [PageSettingController::class, 'about'])->name('admin.ps.about');
    Route::get('/page-settings/apps', [PageSettingController::class, 'apps'])->name('admin.ps.apps');
    Route::get('/page-settings/account', [PageSettingController::class, 'stretegy'])->name('admin.ps.account');
    Route::get('/page-settings/section/heading', [PageSettingController::class, 'sectionHeading'])->name('admin.ps.heading');
    Route::post('/page-settings/contact/update', [PageSettingController::class, 'contactupdate'])->name('admin.ps.contactupdate');
    Route::post('/page-settings/update/all', [PageSettingController::class, 'update'])->name('admin.ps.update');
    //------------ ADMIN PAGE SECTION ------------

  });

  Route::group(['middleware' => 'permissions:Email Setting'], function () {
    Route::get('/email-templates/datatables', [EmailController::class, 'datatables'])->name('admin.mail.datatables');
    Route::get('/email-templates', [EmailController::class, 'index'])->name('admin.mail.index');
    Route::get('/email-templates/{id}', [EmailController::class, 'edit'])->name('admin.mail.edit');
    Route::post('/email-templates/{id}', [EmailController::class, 'update'])->name('admin.mail.update');
    Route::get('/email-config', [EmailController::class, 'config'])->name('admin.mail.config');
    Route::get('/groupemail', [EmailController::class, 'groupemail'])->name('admin.group.show');
    Route::post('/groupemailpost', [EmailController::class, 'groupemailpost'])->name('admin.group.submit');
  });

  Route::group(['middleware' => 'permissions:Message'], function () {
    Route::post('/send/message', [MessageController::class, 'usercontact'])->name('admin.send.message');
    Route::get('/user/ticket', [MessageController::class, 'index'])->name('admin.user.message');
    Route::get('/messages/datatables/', [MessageController::class, 'datatables'])->name('admin.message.datatables');
    Route::get('/message/{id}', [MessageController::class, 'message'])->name('admin.message.show');
    Route::get('/message/{id}/delete', [MessageController::class, 'messagedelete'])->name('admin.message.delete');
    Route::post('/message/post', [MessageController::class, 'postmessage'])->name('admin.message.store');
    Route::get('/message/load/{id}', [MessageController::class, 'messageshow'])->name('admin-message-load');
  });

  Route::group(['middleware' => 'permissions:Currency Setting'], function () {
    Route::get('/general-settings/currency/{status}', [GeneralSettingController::class, 'currency'])->name('admin.gs.iscurrency');
    Route::get('/currency/datatables', [CurrencyController::class, 'datatables'])->name('admin.currency.datatables'); //JSON REQUEST
    Route::get('/currency', [CurrencyController::class, 'index'])->name('admin.currency.index');
    Route::get('/currency/create', [CurrencyController::class, 'create'])->name('admin.currency.create');
    Route::post('/currency/create', [CurrencyController::class, 'store'])->name('admin.currency.store');
    Route::get('/currency/edit/{id}', [CurrencyController::class, 'edit'])->name('admin.currency.edit');
    Route::post('/currency/update/{id}', [CurrencyController::class, 'update'])->name('admin.currency.update');
    Route::get('/currency/delete/{id}', [CurrencyController::class, 'destroy'])->name('admin.currency.delete');
    Route::get('/currency/status/{id1}/{id2}', [CurrencyController::class, 'status'])->name('admin.currency.status');
  });

  Route::group(['middleware' => 'permissions:Crypto Management'], function () {
    Route::get('/crypto/currency/datatables', [CryptoCurrencyController::class, 'datatables'])->name('admin.crypto.currency.datatables'); //JSON REQUEST
    Route::get('/crypto/currency', [CryptoCurrencyController::class, 'index'])->name('admin.crypto.currency.index');
    Route::get('/crypto/currency/create', [CryptoCurrencyController::class, 'create'])->name('admin.crypto.currency.create');
    Route::post('/crypto/currency/create', [CryptoCurrencyController::class, 'store'])->name('admin.crypto.currency.store');
    Route::get('/crypto/currency/edit/{id}', [CryptoCurrencyController::class, 'edit'])->name('admin.crypto.currency.edit');
    Route::post('/crypto/currency/update/{id}', [CryptoCurrencyController::class, 'update'])->name('admin.crypto.currency.update');
    Route::get('/crypto/currency/delete/{id}', [CryptoCurrencyController::class, 'destroy'])->name('admin.crypto.currency.delete');

    Route::get('/deposits/crypto/datatables', [CryptoDepositController::class, 'datatables'])->name('admin.deposits.crypto.datatables');
    Route::get('/deposits/crypto', [CryptoDepositController::class, 'index'])->name('admin.deposits.crypto.index');
    Route::get('/deposits/crypto/status/{id1}/{id2}', [CryptoDepositController::class, 'status'])->name('admin.deposits.crypto.status');

    Route::get('/withdraws/crypto/datatables', [CryptoWithdrawController::class, 'datatables'])->name('admin.withdraws.crypto.datatables');
    Route::get('/withdraws/crypto', [CryptoWithdrawController::class, 'index'])->name('admin.withdraws.crypto.index');
    Route::get('/withdraws/crypto/status/{id1}/{id2}', [CryptoWithdrawController::class, 'status'])->name('admin.withdraws.crypto.status');
    Route::get('/withdraws/crypto/transaction/edit/{id}', [CryptoWithdrawController::class, 'edit'])->name('admin.withdraws.crypto.edit');
    Route::post('/withdraws/crypto/transaction/update/{id}', [CryptoWithdrawController::class, 'update'])->name('admin.withdraws.crypto.update');
  });

  Route::group(['middleware' => 'permissions:KYC Management'], function () {
    Route::get('/manage-kyc/datatables', [KycManageController::class, 'datatables'])->name('admin.manage.kyc.datatables');
    Route::get('/manage-kyc-form', [KycManageController::class, 'index'])->name('admin.manage.kyc');
    Route::get('/manage-kyc-module', [KycManageController::class, 'module'])->name('admin.manage.module');
    Route::get('/manage-kyc-form/{user}', [KycManageController::class, 'userKycForm'])->name('admin.manage.kyc.user');
    Route::post('/manage-kyc-form/{user}', [KycManageController::class, 'kycForm']);
    Route::post('/kyc-form/update', [KycManageController::class, 'kycFormUpdate'])->name('admin.kyc.form.update');
    Route::post('/kyc-form/delete', [KycManageController::class, 'deletedField'])->name('admin.kyc.form.delete');
    Route::get('/kyc-info/{user}', [KycManageController::class, 'kycInfo'])->name('admin.kyc.info');
    Route::get('/kyc-info/user/{id}', [KycManageController::class, 'kycDetails'])->name('admin.kyc.details');
    Route::get('/users/kyc/{id1}/{id2}', [KycManageController::class, 'kyc'])->name('admin.user.kyc');
    Route::get('/users/more/kyc/{id1}/{id2}', [KycManageController::class, 'kyc_more'])->name('admin.more.user.kyc');
    Route::get('/users/kyc/more/details/{id}', [KycManageController::class, 'moreDetails'])->name('admin.more.user.kyc.details');
    Route::get('/user/{id}/kyc_info', [UserController::class, 'profilekycinfo'])->name('admin.user.kycinfo');
    Route::get('/user/kycinfo/datatables/{id}', [UserController::class, 'kycdatatables'])->name('admin.user.kyc.datatables');
    Route::get('/user/kycform/more/{id}', [UserController::class, 'KycForm'])->name('admin.kyc.more.form.create');
    Route::post('/user/kycform/more/store', [UserController::class, 'StoreKycForm'])->name('admin.kyc.more.form.store');
    Route::get('/user/kycinfo/more/datatables/{id}', [UserController::class, 'additionkycdatatables'])->name('admin.user.more.kyc.datatables');

  });

  Route::group(['middleware' => 'permissions:Language Manage'], function () {
    Route::get('/general-settings/language/{status}', [GeneralSettingController::class, 'language'])->name('admin.gs.islanguage');
    Route::get('/languages/datatables', [LanguageController::class, 'datatables'])->name('admin.lang.datatables');
    Route::get('/languages', [LanguageController::class, 'index'])->name('admin.lang.index');
    Route::get('/languages/create', [LanguageController::class, 'create'])->name('admin.lang.create');
    Route::get('/languages/edit/{id}', [LanguageController::class, 'edit'])->name('admin.lang.edit');
    Route::post('/languages/create', [LanguageController::class, 'store'])->name('admin.lang.store');
    Route::post('/languages/edit/{id}', [LanguageController::class, 'update'])->name('admin.lang.update');
    Route::get('/languages/status/{id1}/{id2}', [LanguageController::class, 'status'])->name('admin.lang.st');
    Route::get('/languages/delete/{id}', [LanguageController::class, 'destroy'])->name('admin.lang.delete');


    Route::get('/adminlanguages/datatables', [AdminLanguageController::class, 'datatables'])->name('admin.tlang.datatables');
    Route::get('/adminlanguages', [AdminLanguageController::class, 'index'])->name('admin.tlang.index');
    Route::get('/adminlanguages/create', [AdminLanguageController::class, 'create'])->name('admin.tlang.create');
    Route::get('/adminlanguages/edit/{id}', [AdminLanguageController::class, 'edit'])->name('admin.tlang.edit');
    Route::post('/adminlanguages/create', [AdminLanguageController::class, 'store'])->name('admin.tlang.store');
    Route::post('/adminlanguages/edit/{id}', [AdminLanguageController::class, 'update'])->name('admin.tlang.update');
    Route::get('/adminlanguages/status/{id1}/{id2}', [AdminLanguageController::class, 'status'])->name('admin.tlang.st');
    Route::get('/adminlanguages/delete/{id}', [AdminLanguageController::class, 'destroy'])->name('admin.tlang.delete');
  });

  Route::group(['middleware' => 'permissions:Fonts'], function () {
    Route::get('/fonts/datatables', [FontController::class, 'datatables'])->name('admin.font.datatables');
    Route::get('/fonts', [FontController::class, 'index'])->name('admin.font.index');
    Route::get('/font/create', [FontController::class, 'create'])->name('admin.font.create');
    Route::post('/font/store', [FontController::class, 'store'])->name('admin.font.store');
    Route::get('/font/edit/{id}', [FontController::class, 'edit'])->name('admin.font.edit');
    Route::post('/font/update/{id}', [FontController::class, 'update'])->name('admin.font.update');
    Route::get('/font/status/{id1}/{id2}', [FontController::class, 'status'])->name('admin.font.status');
    Route::get('/font/delete/{id}', [FontController::class, 'destroy'])->name('admin.font.delete');
  });

  Route::group(['middleware' => 'permissions:Menupage Setting'], function () {
    Route::get('/page-settings/contact', [PageSettingController::class, 'contact'])->name('admin.ps.contact');

    Route::get('/page/datatables', [PageController::class, 'datatables'])->name('admin.page.datatables'); //JSON REQUEST
    Route::get('/page', [PageController::class, 'index'])->name('admin.page.index');
    Route::get('/page/create', [PageController::class, 'create'])->name('admin.page.create');
    Route::post('/page/create', [PageController::class, 'store'])->name('admin.page.store');
    Route::get('/page/edit/{id}', [PageController::class, 'edit'])->name('admin.page.edit');
    Route::post('/page/update/{id}', [PageController::class, 'update'])->name('admin.page.update');
    Route::get('/page/delete/{id}', [PageController::class, 'destroy'])->name('admin.page.delete');
    Route::get('/page/status/{id1}/{id2}', [PageController::class, 'status'])->name('admin.page.status');


    Route::get('/faq/datatables', [FaqController::class, 'datatables'])->name('admin.faq.datatables');
    Route::get('/admin-faq', [FaqController::class, 'index'])->name('admin.faq.index');
    Route::get('/faq/create', [FaqController::class, 'create'])->name('admin.faq.create');
    Route::get('/faq/edit/{id}', [FaqController::class, 'edit'])->name('admin.faq.edit');
    Route::get('/faq/delete/{id}', [FaqController::class, 'destroy'])->name('admin.faq.delete');
    Route::post('/faq/update/{id}', [FaqController::class, 'update'])->name('admin.faq.update');
    Route::post('/faq/create', [FaqController::class, 'store'])->name('admin.faq.store');
  });

  Route::group(['middleware' => 'permissions:Seo Tools'], function () {
    Route::get('/seotools/analytics', [SeoToolController::class, 'analytics'])->name('admin.seotool.analytics');
    Route::post('/seotools/analytics/update', [SeoToolController::class, 'analyticsupdate'])->name('admin.seotool.analytics.update');
    Route::get('/seotools/keywords', [SeoToolController::class, 'keywords'])->name('admin.seotool.keywords');
    Route::post('/seotools/keywords/update', [SeoToolController::class, 'keywordsupdate'])->name('admin.seotool.keywords.update');
    Route::get('/products/popular/{id}', [SeoToolController::class, 'popular'])->name('admin.prod.popular');
  });

  Route::group(['middleware' => 'permissions:Sitemaps'], function () {
    Route::get('/sitemap/datatables', [SitemapController::class, 'datatables'])->name('admin.sitemap.datatables');
    Route::get('/sitemap', [SitemapController::class, 'index'])->name('admin.sitemap.index');
    Route::get('/sitemap/create', [SitemapController::class, 'create'])->name('admin.sitemap.create');
    Route::post('/sitemap/store', [SitemapController::class, 'store'])->name('admin.sitemap.store');
    Route::get('/sitemap/{id}/update', [SitemapController::class, 'update'])->name('admin.sitemap.update');
    Route::get('/sitemap/{id}/delete', [SitemapController::class, 'delete'])->name('admin.sitemap.delete');
    Route::post('/sitemap/download', [SitemapController::class, 'download'])->name('admin.sitemap.download');
  });

  Route::group(['middleware' => 'permissions:Subscribers'], function () {
    Route::get('/subscribers/datatables', [SubscriberController::class, 'datatables'])->name('admin.subs.datatables'); //JSON REQUEST
    Route::get('/subscribers', [SubscriberController::class, 'index'])->name('admin.subs.index');
    Route::get('/subscribers/download', [SubscriberController::class, 'download'])->name('admin.subs.download');
  });

  Route::group(['middleware' => 'permissions:Social Setting'], function () {
    //------------ ADMIN SOCIAL SETTINGS SECTION ------------
    Route::get('/social', [SocialSettingController::class, 'index'])->name('admin.social.index');
    Route::post('/social/update', [SocialSettingController::class, 'socialupdate'])->name('admin.social.update');
    Route::post('/social/update/all', [SocialSettingController::class, 'socialupdateall'])->name('admin.social.update.all');
    Route::get('/social/facebook', [SocialSettingController::class, 'facebook'])->name('admin.social.facebook');
    Route::get('/social/google', [SocialSettingController::class, 'google'])->name('admin.social.google');
    Route::get('/social/facebook/{status}', [SocialSettingController::class, 'facebookup'])->name('admin.social.facebookup');
    Route::get('/social/google/{status}', [SocialSettingController::class, 'googleup'])->name('admin.social.googleup');
    //------------ ADMIN SOCIAL SETTINGS SECTION ENDS------------
  });

  Route::get('/check/movescript', [DashboardController::class, 'movescript'])->name('admin-move-script');
  Route::get('/generate/backup', [DashboardController::class, 'generate_bkup'])->name('admin-generate-backup');
  Route::get('/activation', [DashboardController::class, 'activation'])->name('admin-activation-form');
  Route::post('/activation', [DashboardController::class, 'activation_submit'])->name('admin-activate-purchase');
  Route::get('/clear/backup', [DashboardController::class, 'clear_bkup'])->name('admin-clear-backup');
});

