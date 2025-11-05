<?php

use App\Http\Controllers\EventApiController;
use App\Http\Controllers\GuestApiController;
use App\Http\Controllers\PhotoApiController;
use Illuminate\Support\Facades\Route;

// Event metadata (public)
Route::get('/events/{event}', [EventApiController::class, 'show']);

// Guest authentication
Route::post('/events/{event}/auth', [GuestApiController::class, 'authenticate']);

// Photo upload (requires authentication)
Route::post('/events/{event}/photos', [PhotoApiController::class, 'store'])
    ->middleware('auth:sanctum');
