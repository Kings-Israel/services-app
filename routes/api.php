<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/forgot-password', [RegisteredUserController::class, 'sendResetPasswordCode']);
Route::post('/otp/validate', [RegisteredUserController::class, 'validateOtp']);
Route::post('/reset-password', [RegisteredUserController::class, 'resetPassword']);

Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [UserController::class, 'show']);
    Route::post('/services/{latitude?}/{longitude?}', [ServiceController::class, 'store']);
    Route::post('/services/{id}', [ServiceController::class, 'update']);

    Route::group(['prefix' => 'admin/'], function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::get('users', [UserController::class, 'index']);
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
});
