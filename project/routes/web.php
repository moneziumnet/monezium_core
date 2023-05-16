<?php

use App\Http\Controllers;
use App\Http\Controllers\API\AccessController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Frontend\FrontendController;
use App\Http\Controllers\User\KYCController;
use App\Http\Controllers\User\UserTelegramController;
use App\Http\Controllers\User\UserWhatsappController;
use Illuminate\Http\Request;
use App\Http\Controllers\API\QRAccessController;
use App\Http\Controllers\User\ClearJunctionCallBackController;
use App\Http\Controllers\Deposit\TribePaymentController;
use App\Models\User;
use App\Http\Controllers\Chatify\MessagesController;

Route::redirect('admin', 'admin/login');
Route::redirect('user', 'user/login');

Route::webhooks('webhook-openpayd','openpayd');
Route::webhooks('webhook-railsbank','railsbank');
Route::webhooks('webhook-swan','swan');
Route::post('/cj-payin', [ClearJunctionCallBackController::class, 'payin'])->name('cj-payin');
Route::post('/cj-payout', [ClearJunctionCallBackController::class, 'payout'])->name('cj-payout');

Route::post('/iban-create-completed', [TribePaymentController::class, 'account_webhook'])->name('tribe-account-completed');

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
Route::get('/access/gateway', [AccessController::class, 'gateway_pay'])->name('api.pay.gateway');
Route::post('/access/submit', [AccessController::class, 'pay_submit'])->name('api.pay.submit');
Route::get('/access/paypal/success/{shop_id}/{item_number}', [AccessController::class, 'notify'])->name('api.pay.paypal.success');
Route::get('/access/paypal/cancel/{shop_id}/{item_number}', [AccessController::class, 'cancel'])->name('api.pay.paypal.cancel');

Route::get('blogs', [FrontendController::class, 'blog'])->name('front.blog');
Route::get('blog/{slug}', [FrontendController::class, 'blogdetails'])->name('blog.details');
Route::get('/blog-search', [FrontendController::class, 'blogsearch'])->name('front.blogsearch');
Route::get('/blog/category/{slug}', [FrontendController::class, 'blogcategory'])->name('front.blogcategory');
Route::get('/blog/tag/{slug}', [FrontendController::class, 'blogtags'])->name('front.blogtags');
Route::get('/blog/archive/{slug}', [FrontendController::class, 'blogarchive'])->name('front.blogarchive');

Route::get('/services', [FrontendController::class, 'services'])->name('front.services');

Route::get('/term-service', [FrontendController::class, 'termService'])->name('front.term-service');


Route::prefix('chatify')->group(function () {

    Route::get('/', [MessagesController::class, 'index'])->name(config('chatify.routes.prefix'));
    /*
    * This is the main app route [Chatify Messenger]
    */
    Route::get('/', [MessagesController::class, 'index'])->name(config('chatify.routes.prefix'));

    /**
     *  Fetch info for specific id [user/group]
     */
    Route::post('/idInfo', [MessagesController::class, 'idFetchData']);

    /**
     * Send message route
     */
    Route::post('/sendMessage', [MessagesController::class, 'send'])->name('send.message');

    /**
     * Fetch messages
     */
    Route::post('/fetchMessages', [MessagesController::class, 'fetch'])->name('fetch.messages');

    /**
     * Download attachments route to create a downloadable links
     */
    Route::get('/download/{fileName}', [MessagesController::class, 'download'])->name(config('chatify.attachments.download_route_name'));

    /**
     * Authentication for pusher private channels
     */
    Route::post('/chat/auth', [MessagesController::class, 'pusherAuth'])->name('pusher.auth');

    /**
     * Make messages as seen
     */
    Route::post('/makeSeen', [MessagesController::class, 'seen'])->name('messages.seen');

    /**
     * Get contacts
     */
    Route::get('/getContacts', [MessagesController::class, 'getContacts'])->name('contacts.get');

    /**
     * Update contact item data
     */
    Route::post('/updateContacts', [MessagesController::class, 'updateContactItem'])->name('contacts.update');


    /**
     * Star in favorite list
     */
    Route::post('/star', [MessagesController::class, 'favorite'])->name('star');

    /**
     * get favorites list
     */
    Route::post('/favorites', [MessagesController::class, 'getFavorites'])->name('favorites');

    /**
     * Search in messenger
     */
    Route::get('/search', [MessagesController::class, 'search'])->name('search');

    /**
     * Get shared photos
     */
    Route::post('/shared', [MessagesController::class, 'sharedPhotos'])->name('shared');

    /**
     * Delete Conversation
     */
    Route::post('/deleteConversation', [MessagesController::class, 'deleteConversation'])->name('conversation.delete');

    /**
     * Delete Message
     */
    Route::post('/deleteMessage', [MessagesController::class, 'deleteMessage'])->name('message.delete');

    /**
     * Update setting
     */
    Route::post('/updateSettings', [MessagesController::class, 'updateSettings'])->name('avatar.update');

    /**
     * Set active status
     */
    Route::post('/setActiveStatus', [MessagesController::class, 'setActiveStatus'])->name('activeStatus.set');


    /*
    * [Group] view by id
    */
    Route::get('/group/{id}', [MessagesController::class, 'index'])->name('group');

    /*
    * user view by id.
    * Note : If you added routes after the [User] which is the below one,
    * it will considered as user id.
    *
    * e.g. - The commented routes below :
    */
// Route::get('/route', function(){ return 'Munaf'; }); // works as a route
    Route::get('/{id}', [MessagesController::class, 'index'])->name('user');
// Route::get('/route', function(){ return 'Munaf'; }); // works as a user id
});

Route::get('/about', [FrontendController::class, 'about'])->name('front.about');
Route::get('/contact', [FrontendController::class, 'contact'])->name('front.contact');
Route::post('/contact', [FrontendController::class, 'contactemail'])->name('front.contact.submit');
Route::get('/faq', [FrontendController::class, 'faq'])->name('front.faq');
Route::get('/{slug}', [FrontendController::class, 'page'])->name('front.page');
Route::post('/subscriber', [FrontendController::class, 'subscriber'])->name('front.subscriber');


Route::get('/currency/{id}', [FrontendController::class, 'currency'])->name('front.currency');
Route::get('/language/{id}', [FrontendController::class, 'language'])->name('front.language');
Route::get('kyc-take-selfie/{id}', [KYCController::class,'onlineSelfie'])->name('user.kyc.selfie');

Route::get('/qr/access', [QRAccessController::class, 'index'])->name('qr.pay.index');
Route::get('/qr/access/crypto', [QRAccessController::class, 'crypto_pay'])->name('qr.pay.crypto');
Route::post('/qr/access/submit', [QRAccessController::class, 'pay_submit'])->name('qr.pay.submit');

Route::get('/telegram/login', [UserTelegramController::class, 'bot_login'])->name('user.telegram.login');
Route::get('/telegram/logout', [UserTelegramController::class, 'bot_logout'])->name('user.telegram.logout');
Route::post('/telegram/inbound', [UserTelegramController::class, 'inbound'])->name('user.telegram.inbound');

Route::post('/whatsapp/inbound', [UserWhatsappController::class, 'inbound'])->name('user.whatsapp.inbound');
Route::post('/whatsapp/status', [UserWhatsappController::class, 'status'])->name('user.whatsapp.status');
Route::get('/user/crypto/deposit/sms', [UserTelegramController::class,'crypto_deposit_sms'])->name('user.telegram.crypto.deposit.sms');

