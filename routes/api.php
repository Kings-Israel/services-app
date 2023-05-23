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

Route::get('/services/{latitude?}/{longitude?}', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [UserController::class, 'show']);
    Route::post('/services', [ServiceController::class, 'store']);
    Route::post('/services/{id}/update', [ServiceController::class, 'update']);
    Route::post('/services/{id}/images/add', [ServiceController::class, 'saveServiceImages']);

    Route::group(['prefix' => 'admin/'], function () {
        Route::get('users', [UserController::class, 'index']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::post('/categories/{id}/update', [CategoryController::class, 'update']);
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
});
