<?php

use App\Http\Controllers\Backend\Pos\StkPushController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Safaricom Daraja M-Pesa Routes
Route::post('/mpesa/stk-push', [StkPushController::class, 'initiate']);
Route::post('/mpesa/callback', [StkPushController::class, 'callback']);
Route::get('/mpesa/status/{order_id}', [StkPushController::class, 'status']);
