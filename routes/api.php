<?php

use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/doctors/{doctor}/available-slots', [AvailabilityController::class, 'slots'])
    ->name('api.doctors.slots');

Route::get('/doctors/available', [AvailabilityController::class, 'doctors'])
    ->name('api.doctors.available');

Route::get('/patients/search', [AvailabilityController::class, 'patients'])
    ->name('api.patients.search');

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('api.stripe.webhook');
