<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Staff\LoginController;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\UserController;
use App\Http\Controllers\Staff\KycManageController;


Route::prefix('staff')->group(function () {

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('staff.login');
    Route::post('/login', [LoginController::class, 'login'])->name('staff.login.submit');
    Route::get('/forgot', [LoginController::class, 'showForgotForm'])->name('staff.forgot');
    Route::post('/forgot-submit', [LoginController::class, 'forgot'])->name('staff.forgot.submit');
    Route::get('/change-password/{token}', [LoginController::class, 'showChangePassForm'])->name('staff.change.token');
    Route::post('/change-password', [LoginController::class, 'changepass'])->name('staff.change.password');
    Route::get('/logout', [LoginController::class, 'logout'])->name('staff.logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('staff.dashboard');

    Route::get('/users', [UserController::class, 'index'])->name('staff.user.index');
    Route::get('/users/datatables', [UserController::class, 'datatables'])->name('staff-user-datatables');

    Route::get('/user/profile/{id}', [UserController::class, 'profileInfo'])->name('staff-user-profile');
    Route::get('/user/kyc_info/{id}', [UserController::class, 'profilekycinfo'])->name('staff.user.kycinfo');
    Route::get('/user/kycinfo/more/datatables/{id}', [UserController::class, 'additionkycdatatables'])->name('staff.user.more.kyc.datatables');
    Route::get('/user/kycform/more/{id}', [UserController::class, 'KycForm'])->name('staff.kyc.more.form.create');
    Route::post('/user/kycform/more/store', [UserController::class, 'StoreKycForm'])->name('staff.kyc.more.form.store');

    Route::get('/users/kyc/{id1}/{id2}', [KycManageController::class, 'kyc'])->name('staff.user.kyc');
    Route::get('/kyc-info/user/{id}', [KycManageController::class, 'kycDetails'])->name('staff.kyc.details');
    Route::post('/kyc-form/add/more', [KycManageController::class, 'add_more_form'])->name('staff.manage.kyc.add.more');
    Route::get('/users/more/kyc/{id1}/{id2}', [KycManageController::class, 'kyc_more'])->name('staff.more.user.kyc');
    Route::get('/users/kyc/more/details/{id}', [KycManageController::class, 'moreDetails'])->name('staff.more.user.kyc.details');


});

