<?php

use App\Http\Controllers\API\FoodController;
use App\Http\Controllers\API\MidtransController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\TranscationController;
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

// Beberapa api yang bisa di akses oleh user berada di dalam bloc kode dbwh ini :
Route::middleware('auth:sanctum')->group(function(){
    // ini cuma bisa di akses kalau kalian sudah login
    // pertama yang ditangkap adalah data pro :
    Route::get('user', [UserController::class, 'fetch']);
    Route::post('user', [UserController::class, 'updateProfile']);
    Route::post('user', [UserController::class, 'updatePhoto']);
    Route::post('logout', [UserController::class, 'logout']);

    Route::post('checkout', [TranscationController::class, 'checkout']);

    Route::get('transaction', [TranscationController::class, 'all']);
    Route::post('transcation', [TranscationController::class, 'update']);
});

// Membuat endpointnya :
Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);

Route::get('food', [FoodController::class, 'all']);

Route::post('midtrans/callback', [MidtransController::class, 'callback']);



