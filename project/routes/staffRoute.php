<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Staff\LoginController;
use App\Http\Controllers\Staff\DashboardController;


Route::prefix('staff')->group(function () {

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('staff.login');
    Route::post('/login', [LoginController::class, 'login'])->name('staff.login.submit');
    Route::get('/forgot', [LoginController::class, 'showForgotForm'])->name('staff.forgot');
    Route::post('/forgot-submit', [LoginController::class, 'forgot'])->name('staff.forgot.submit');
    Route::get('/change-password/{token}', [LoginController::class, 'showChangePassForm'])->name('staff.change.token');
    Route::post('/change-password', [LoginController::class, 'changepass'])->name('staff.change.password');
    Route::get('/logout', [LoginController::class, 'logout'])->name('staff.logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('staff.dashboard');


});

