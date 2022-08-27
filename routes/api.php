<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\GroupConversationController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserConversationsController;
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

Route::controller(AuthController::class)
    ->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::post('/logout', 'logout');
        Route::post('/verify', 'verify');
        Route::post('/resent_code', 'resent_code');
        Route::get('/profile', 'profile');
        Route::put('/profile', 'edit_profile');
    });

Route::prefix('/conversation')->controller(ConversationController::class)
    ->group(function () {
        Route::post('/', 'store');
        Route::post('/read', 'makeConversationReaded');
        Route::get('/{conversation}', 'show');
        Route::get('/', 'index');
    });

Route::prefix('/message')->controller(MessageController::class)
    ->group(function () {
        Route::post('/', 'store');
        Route::put('/{msg}', 'update');
        Route::delete('/{msg}', 'delete');
    });

