<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\MuseumController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('artifacts', [MuseumController::class, 'artifacts']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'not_blocked'])->group(function (): void {
    Route::get('submissions', [MuseumController::class, 'submissions']);
    Route::post('submissions', [MuseumController::class, 'storeSubmission']);
    Route::get('orders', [MuseumController::class, 'orders']);
    Route::post('artifacts/{artifact}/orders', [MuseumController::class, 'storeOrder']);
});

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'admin'])
    ->group(function (): void {
        Route::post('categories', [AdminCategoryController::class, 'store']);
    });
