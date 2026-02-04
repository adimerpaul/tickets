<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\EventoHorarioController;
use App\Http\Controllers\EventoPrecioController;
use App\Http\Controllers\MonedaController;

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

// eventos
    Route::get('/eventos', [EventoController::class, 'index']);
    Route::get('/eventos/{evento}', [EventoController::class, 'show']);
    Route::get('/eventos/slug/{slug}', [EventoController::class, 'showBySlug']);
    Route::post('/eventos', [EventoController::class, 'store']);
    Route::put('/eventos/{evento}', [EventoController::class, 'update']);
    Route::delete('/eventos/{evento}', [EventoController::class, 'destroy']);

    Route::get('/eventosMenu', [EventoController::class, 'menu']);


//    Route::prefix('eventos/{evento}')->group(function () {
//        Route::get('horarios/month', [EventoHorarioController::class, 'month']);   // para pintar calendario
//        Route::get('horarios/day',   [EventoHorarioController::class, 'day']);     // lista lateral del día
//        Route::post('horarios/generate', [EventoHorarioController::class, 'generate']); // generar rango
//
//        // nacionalidades
//        Route::get('nacionalidades', [EventoPrecioController::class, 'nacionalidadesIndex']);
//        Route::post('nacionalidades', [EventoPrecioController::class, 'nacionalidadesStore']);
//
//        // tipos entrada
//        Route::get('tipos-entrada', [EventoPrecioController::class, 'tiposIndex']);
//        Route::post('tipos-entrada', [EventoPrecioController::class, 'tiposStore']);
//
//        // precios
//        Route::get('precios', [EventoPrecioController::class, 'preciosIndex']);          // ?segmento=ADULTO
//        Route::post('precios/upsert', [EventoPrecioController::class, 'preciosUpsert']); // bulk
//    });
//
//    Route::put('evento-horarios/{horario}', [EventoHorarioController::class, 'update']);   // editar slot
//    Route::delete('evento-horarios/{horario}', [EventoHorarioController::class, 'destroy']); // borrar slot
//
//    // updates/deletes directos
//    Route::put('evento-nacionalidades/{nac}', [EventoPrecioController::class, 'nacionalidadesUpdate']);
//    Route::delete('evento-nacionalidades/{nac}', [EventoPrecioController::class, 'nacionalidadesDestroy']);
//
//    Route::put('evento-tipos-entrada/{tipo}', [EventoPrecioController::class, 'tiposUpdate']);
//    Route::delete('evento-tipos-entrada/{tipo}', [EventoPrecioController::class, 'tiposDestroy']);

    // MONEDAS (global)
    Route::get('/monedas', [MonedaController::class, 'index']);
    Route::post('/monedas', [MonedaController::class, 'store']);
    Route::put('/monedas/{moneda}', [MonedaController::class, 'update']);
    Route::delete('/monedas/{moneda}', [MonedaController::class, 'destroy']);

    Route::prefix('eventos/{evento}')->group(function () {
        Route::get('horarios/month', [EventoHorarioController::class, 'month']);   // para pintar calendario
        Route::get('horarios/day',   [EventoHorarioController::class, 'day']);     // lista lateral del día
        Route::post('horarios/generate', [EventoHorarioController::class, 'generate']); // generar rango

        // nacionalidades
        Route::get('nacionalidades', [EventoPrecioController::class, 'nacionalidadesIndex']);
        Route::post('nacionalidades', [EventoPrecioController::class, 'nacionalidadesStore']);

        // tipos entrada
        Route::get('tipos-entrada', [EventoPrecioController::class, 'tiposIndex']);
        Route::post('tipos-entrada', [EventoPrecioController::class, 'tiposStore']);

        // segmentos
        Route::get('segmentos', [EventoPrecioController::class, 'segmentosIndex']);
        Route::post('segmentos', [EventoPrecioController::class, 'segmentosStore']);

        // precios
        Route::get('precios', [EventoPrecioController::class, 'preciosIndex']);
        Route::post('precios/upsert', [EventoPrecioController::class, 'preciosUpsert']);
    });
        Route::put('evento-horarios/{horario}', [EventoHorarioController::class, 'update']);   // editar slot
    Route::delete('evento-horarios/{horario}', [EventoHorarioController::class, 'destroy']); // borrar slot

    // updates/deletes directos
    Route::put('evento-nacionalidades/{nac}', [EventoPrecioController::class, 'nacionalidadesUpdate']);
    Route::delete('evento-nacionalidades/{nac}', [EventoPrecioController::class, 'nacionalidadesDestroy']);

    Route::put('evento-tipos-entrada/{tipo}', [EventoPrecioController::class, 'tiposUpdate']);
    Route::delete('evento-tipos-entrada/{tipo}', [EventoPrecioController::class, 'tiposDestroy']);

    Route::put('evento-segmentos/{seg}', [EventoPrecioController::class, 'segmentosUpdate']);
    Route::delete('evento-segmentos/{seg}', [EventoPrecioController::class, 'segmentosDestroy']);

});
Route::get(
    'eventos/{evento}/checkout-data',
    [\App\Http\Controllers\EventoCheckoutController::class, 'checkoutData']
);
