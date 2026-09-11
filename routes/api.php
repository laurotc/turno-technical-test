<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ShippingLabelController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/labels', [ShippingLabelController::class, 'index'])->name('api.labels.index');
    Route::post('/labels', [ShippingLabelController::class, 'store'])->name('api.labels.store');
    Route::get('/labels/{shippingLabel}', [ShippingLabelController::class, 'show'])->name('api.labels.show');
    Route::get('/labels/{shippingLabel}/print', [ShippingLabelController::class, 'print'])->name('api.labels.print');
});
