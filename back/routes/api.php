<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeController;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::post('/stripe/checkout', [StripeController::class, 'checkout']);
Route::post('/stripe/webhook', [StripeController::class, 'webhook']); // webhook público
