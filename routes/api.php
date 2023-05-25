<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ServiceBookmarkController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\ServiceReviewController;
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
Route::post('/google/authenticate', [AuthenticatedSessionController::class, 'googleAuthenticate']);

Route::get('/services/{latitude?}/{longitude?}', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

// Reviews
Route::get('/reviews', [ServiceReviewController::class, 'reviews']);
Route::get('/reviews/{id}', [ServiceReviewController::class, 'review']);

Route::middleware(['auth:sanctum'])->group(function () {
    // User
    Route::get('/user', [UserController::class, 'show']);

    // Services
    Route::post('/services', [ServiceController::class, 'store']);
    Route::post('/services/{id}/update', [ServiceController::class, 'update']);
    Route::post('/services/{id}/images/add', [ServiceController::class, 'saveServiceImages']);

    // Request a new service
    Route::post('/service/request', [ServiceRequestController::class, 'requestService']);
    // Update service request status
    Route::post('/service/delivery/status/update', [ServiceRequestController::class, 'updateServiceRequestStatus']);

    // Service Requests
    Route::get('/service/requests', [ServiceRequestController::class, 'index']);

    // Bookmarks
    Route::get('/bookmarks', [ServiceBookmarkController::class, 'bookmarks']);
    Route::get('/bookmarks/{id}', [ServiceBookmarkController::class, 'bookmark']);
    Route::post('/bookmark-service', [ServiceBookmarkController::class, 'bookmarkService']);

    // Reviews
    Route::post('/review', [ServiceReviewController::class, 'store']);
    Route::post('/review/{id}', [ServiceReviewController::class, 'update']);

    // Admin
    Route::group(['prefix' => 'admin/', 'as' => 'admin.'], function () {
        // Users
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        // Categories
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::post('/categories/{id}/update', [CategoryController::class, 'update'])->name('categories.update');
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
});
