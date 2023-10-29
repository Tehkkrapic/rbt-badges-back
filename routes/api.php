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
    Route::get('/blockchains', [BlockchainController::class, 'index']);

    Route::get('/badges/{blockchain}', [BadgeController::class, 'index']);
    Route::post('/badges/mint', [BadgeController::class, 'mint']);
    Route::post('/badges/refresh', [BadgeController::class, 'refresh']);
    Route::put('/badges/sentToAddress/{id}', [BadgeController::class, 'updateSentToAddress']);
});
