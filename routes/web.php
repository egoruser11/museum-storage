<?php

use App\Http\Controllers\Admin\ArtifactController as AdminArtifactController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderReviewController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SubmissionReviewController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('catalog/{artifact}', [CatalogController::class, 'show'])->name('catalog.show');

Route::middleware('guest')->group(function (): void {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.store');
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'not_blocked'])->group(function (): void {
    Route::get('my/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
    Route::post('my/submissions', [SubmissionController::class, 'store'])->name('submissions.store');
    Route::get('my/orders', [PurchaseOrderController::class, 'index'])->name('orders.index');
    Route::post('catalog/{artifact}/orders', [PurchaseOrderController::class, 'store'])->name('orders.store');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function (): void {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('categories', AdminCategoryController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
        Route::resource('artifacts', AdminArtifactController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('users/{user}/block', [AdminUserController::class, 'block'])->name('users.block');
        Route::patch('users/{user}/unblock', [AdminUserController::class, 'unblock'])->name('users.unblock');
        Route::get('submissions', [SubmissionReviewController::class, 'index'])->name('submissions.index');
        Route::patch('submissions/{artifactSubmission}', [SubmissionReviewController::class, 'update'])->name('submissions.update');
        Route::delete('submissions/{artifactSubmission}', [SubmissionReviewController::class, 'destroy'])->name('submissions.destroy');
        Route::get('orders', [OrderReviewController::class, 'index'])->name('orders.index');
        Route::patch('orders/{purchaseOrder}', [OrderReviewController::class, 'update'])->name('orders.update');
        Route::delete('orders/{purchaseOrder}', [OrderReviewController::class, 'destroy'])->name('orders.destroy');
        Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
    });
