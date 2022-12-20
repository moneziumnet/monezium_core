<?php

use App\Http\Controllers;
use App\Http\Controllers\API\AccessController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Frontend\FrontendController;
use App\Http\Controllers\User\ManageInvoiceController;
use App\Http\Controllers\User\UserContractManageController;
use App\Http\Controllers\User\KYCController;
use App\Http\Controllers\User\MerchantCheckoutController;
use App\Http\Controllers\User\MerchantProductController;
use App\Http\Controllers\User\MerchantCampaignController;
use Illuminate\Http\Request;
use App\Http\Controllers\API\QRAccessController;
use App\Handler\ClearJunctionResponse;
use App\Models\User;

Route::redirect('admin', 'admin/login');

Route::webhooks('webhook-openpayd','openpayd');
Route::webhooks('webhook-railsbank','railsbank');
Route::webhooks('webhook-swan','swan');
Route::post('cj-payin', [ClearJunctionResponse::class, 'payin']);
Route::post('cj-payout', [ClearJunctionResponse::class, 'payout']);

Route::get('check-user-plan/{key}', function($key){
    if($key == env('APP_KEY')) {
        $user_list = User::all();
        foreach($user_list as $user) {
            wallet_monthly_fee($user->id);
        }
        return 'success';
    } else {
        return 'error';
    }
});

Route::post('the/genius/ocean/2441139', [FrontendController::class, 'subscription']);
Route::get('finalize', [FrontendController::class, 'finalize']);

Route::get('/', [FrontendController::class, 'index'])->name('front.index');

Route::get('/access', [AccessController::class, 'index'])->name('api.pay.index');
Route::get('/access/login', [AccessController::class, 'login'])->name('api.pay.login');
Route::post('/access/login_submit', [AccessController::class, 'login_submit'])->name('api.pay.login.submit');
Route::get('/access/crypto', [AccessController::class, 'crypto_pay'])->name('api.pay.crypto');
Route::post('/access/submit', [AccessController::class, 'pay_submit'])->name('api.pay.submit');

Route::get('blogs', [FrontendController::class, 'blog'])->name('front.blog');
Route::get('blog/{slug}', [FrontendController::class, 'blogdetails'])->name('blog.details');
Route::get('/blog-search', [FrontendController::class, 'blogsearch'])->name('front.blogsearch');
Route::get('/blog/category/{slug}', [FrontendController::class, 'blogcategory'])->name('front.blogcategory');
Route::get('/blog/tag/{slug}', [FrontendController::class, 'blogtags'])->name('front.blogtags');
Route::get('/blog/archive/{slug}', [FrontendController::class, 'blogarchive'])->name('front.blogarchive');

Route::get('/services', [FrontendController::class, 'services'])->name('front.services');

Route::get('/term-service', [FrontendController::class, 'termService'])->name('front.term-service');
Route::get('/about', [FrontendController::class, 'about'])->name('front.about');
Route::get('/contact', [FrontendController::class, 'contact'])->name('front.contact');
Route::post('/contact', [FrontendController::class, 'contactemail'])->name('front.contact.submit');
Route::get('/faq', [FrontendController::class, 'faq'])->name('front.faq');
Route::get('/{slug}', [FrontendController::class, 'page'])->name('front.page');
Route::post('/subscriber', [FrontendController::class, 'subscriber'])->name('front.subscriber');
Route::get('view-invoice/{number}',   [ManageInvoiceController::class,'invoiceView'])->name('invoice.view');
Route::get('pay-invoice/{number}',   [ManageInvoiceController::class,'invoicePaymentByLink'])->name('invoice.pay');
Route::get('view-contract/{id}/{role}',   [UserContractManageController::class,'contract_view'])->name('contract.view');
Route::get('view-aoa/{id}/{role}',   [UserContractManageController::class,'aoa_sign_view'])->name('aoa.view');
Route::get('/currency/{id}', [FrontendController::class, 'currency'])->name('front.currency');
Route::get('/language/{id}', [FrontendController::class, 'language'])->name('front.language');
Route::get('/merchant/checkout/link/{id}',[MerchantCheckoutController::class,'link'])->name('user.merchant.checkout.link');
Route::get('/merchant/checkout/link_pay/{id}',[MerchantCheckoutController::class,'link_pay'])->name('user.merchant.checkout.link_pay');
Route::get('/merchant/product/link/{id}', [MerchantProductController::class,'link'])->name('user.merchant.product.link');
Route::get('/merchant/product/link/crypto/{id}', [MerchantProductController::class,'crypto_link'])->name('user.merchant.product.crypto.link');
Route::get('/merchant/product/link/crypto/pay/{id}', [MerchantProductController::class,'crypto_link_pay'])->name('user.merchant.product.crypto.link.pay');
Route::get('/merchant/campaign/link/{id}', [MerchantCampaignController::class,'link'])->name('user.merchant.campaign.link');
Route::get('/merchant/campaign/link/crypto/{id}', [MerchantCampaignController::class,'crypto_link'])->name('user.merchant.campaign.crypto.link');
Route::get('/merchant/campaign/link/crypto/pay/{id}', [MerchantCampaignController::class,'crypto_link_pay'])->name('user.merchant.campaign.crypto.link.pay');
Route::get('kyc-take-selfie/{id}', [KYCController::class,'onlineSelfie'])->name('user.kyc.selfie');

Route::get('/qr/access', [QRAccessController::class, 'index'])->name('qr.pay.index');
Route::get('/qr/access/crypto', [QRAccessController::class, 'crypto_pay'])->name('qr.pay.crypto');
Route::post('/qr/access/submit', [QRAccessController::class, 'pay_submit'])->name('qr.pay.submit');
