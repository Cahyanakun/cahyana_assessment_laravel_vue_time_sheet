<?php

use App\Http\Controllers\Api\TimeEntryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/time-entries', [TimeEntryController::class, 'store']);
Route::get('/time-entries', [TimeEntryController::class, 'index']);
Route::put('/time-entries/{id}', [TimeEntryController::class, 'update']);
Route::get('/init-data', [TimeEntryController::class, 'getInitData']);
