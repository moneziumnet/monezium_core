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
    Route::post('/user/loan-plan', [UserController::class,'loanplan']);
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


});
