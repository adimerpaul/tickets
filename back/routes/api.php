<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\EventoController;

Route::post('/stripe/checkout', [StripeController::class, 'checkout']);
Route::post('/stripe/webhook', [StripeController::class, 'webhook']);

Route::post('/login', [App\Http\Controllers\UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [App\Http\Controllers\UserController::class, 'me']);
    Route::post('/logout', [App\Http\Controllers\UserController::class, 'logout']);

    Route::get('/users', [App\Http\Controllers\UserController::class, 'index']);
    Route::post('/users', [App\Http\Controllers\UserController::class, 'store']);
    Route::put('/users/{user}', [App\Http\Controllers\UserController::class, 'update']);
    Route::delete('/users/{user}', [App\Http\Controllers\UserController::class, 'destroy']);

    Route::put('/updatePassword/{user}', [App\Http\Controllers\UserController::class, 'updatePassword']);
    Route::post('/{user}/avatar', [App\Http\Controllers\UserController::class, 'updateAvatar']);

    Route::get('/permissions', [App\Http\Controllers\UserController::class, 'permissions']);
    Route::get('/users/{user}/permissions', [App\Http\Controllers\UserController::class, 'userPermissions']);
    Route::put('/users/{user}/permissions', [App\Http\Controllers\UserController::class, 'updateUserPermissions']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/stats', [OrderController::class, 'stats']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);

    // PDF (opcional backend)
    Route::get('/orders/{order}/pdfEntradas', [OrderController::class, 'pdfEntradas']);
//    await this.$axios.post(`orders/${o.id}/sendEmail`)
    Route::post('/orders/{order}/sendEmail', [OrderController::class, 'sendEmailWithEntradasPdf']);
    Route::get('/orders-pdf', [OrderController::class, 'pdfList']); // pdf del listado filtrado

    Route::get('/eventos', [EventoController::class, 'index']);
    Route::post('/eventos', [EventoController::class, 'store']);
    Route::get('/eventos/{evento}', [EventoController::class, 'show']);
    Route::put('/eventos/{evento}', [EventoController::class, 'update']);
    Route::delete('/eventos/{evento}', [EventoController::class, 'destroy']);

// Para el frontend /evento/:site
    Route::get('/eventos-slug/{slug}', [EventoController::class, 'showBySlug']);

// Horarios (1 a N)
    Route::post('/eventos/{evento}/horarios', [EventoController::class, 'horariosStore']);
    Route::put('/evento-horarios/{horario}', [EventoController::class, 'horariosUpdate']);
    Route::delete('/evento-horarios/{horario}', [EventoController::class, 'horariosDestroy']);
});
