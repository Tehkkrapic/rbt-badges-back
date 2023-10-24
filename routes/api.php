<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\BlockchainController;
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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware(['auth:sanctum', 'isAdmin'])->group(function () {
    // Route::get('/users', [UserController::class, 'index']);
    // Route::get('/users/{user}', [UserController::class, 'show']);   
    // Route::post('/users/{user}', [UserController::class, 'store']);
    // Route::post('/users/{user}', [UserController::class, 'store']);
    // Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('canDeleteUser');
    // Route::post('/verify/{user}', [UserController::class, 'verify']);

    Route::get('/blockchains', [BlockchainController::class, 'index']);

    Route::get('/badges/{blockchain}', [BadgeController::class, 'index']);
    Route::post('/badges/mint', [BadgeController::class, 'mint']);
//    Route::post('/badges', [BadgeController::class, 'store']);
    Route::post('/badges/refresh', [BadgeController::class, 'refresh']);
    Route::put('/badges/sentToAddress/{id}', [BadgeController::class, 'updateSentToAddress']);
});
