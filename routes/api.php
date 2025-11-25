<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/appointments', [AppointmentController::class, 'index']); 
Route::get('/appointments/{id}', [AppointmentController::class, 'show']); 
Route::post('/appointments', [AppointmentController::class, 'store']);

Route::post('/appointments/{id}/initiate-payment', [PaymentController::class, 'initiatePayment']);
Route::post('/payments/callback', [PaymentController::class, 'handleCallback']);