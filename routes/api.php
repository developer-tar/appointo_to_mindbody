<?php

use App\Http\Controllers\Shopify\AppointoBookingController;
use App\Http\Controllers\Shopify\MindbodyController;
use App\Http\Controllers\Shopify\TestController;
use Illuminate\Support\Facades\Route;

// routes/api.php
Route::prefix('shopify')->group(function () {

    /* ––– Appointo ––– */
    Route::controller(AppointoBookingController::class)
        ->prefix('appointo')->group(function () {
            Route::post('/bookings', 'store');
            Route::post('/bookings/reschedule', 'reschedule');
            Route::post('/bookings/cancel', 'cancel');
            Route::get('/bookings', 'index');
        });

    /* ––– Mindbody ––– */
    Route::controller(MindbodyController::class)
        ->prefix('mindbody')->group(function () {
            Route::get('/appointment-types', 'appointmentTypes');
            Route::post('/book', 'book');
        });
    Route::controller(TestController::class)
        ->prefix('appointo_to_mindbody')->group(function () {
            Route::get('/get_record', 'getRecord');
        });
});
