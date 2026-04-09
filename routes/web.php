<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportsController;

/* ─── Auth ─── */
Route::get('/',        [AuthController::class, 'showLogin']);
Route::post('/login',  [AuthController::class, 'login'])->name('login');
Route::get('/logout',  [AuthController::class, 'logout'])->name('logout');

/* ─── Dashboard ─── */
Route::get('/dashboard', [AuthController::class, 'showDashboard'])->name('dashboard');

/* ─── Products ─── */
Route::get('/products',         [AuthController::class, 'getProducts']);
Route::post('/products',        [AuthController::class, 'addProduct']);
Route::put('/products/{id}',    [AuthController::class, 'updateProduct']);
Route::delete('/products/{id}', [AuthController::class, 'deleteProduct']);

/* ─── Change Password (dashboard) ─── */
Route::post('/change-password', [AuthController::class, 'changePassword'])->name('change.password');

/* ─── Forgot / Reset Password ─── */
Route::get('/forgot-password',        [AuthController::class, 'showForgotPassword'])->name('forgot.password');
Route::post('/forgot-password',       [AuthController::class, 'sendResetLink'])->name('forgot.password.send');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('reset.password');
Route::post('/reset-password',        [AuthController::class, 'resetPassword'])->name('reset.password.submit');

/* ─── Categories ─── */
Route::get('/categories',        [AuthController::class, 'getCategories']);
Route::post('/categories',       [AuthController::class, 'addCategory']);
Route::delete('/categories/{id}',[AuthController::class, 'deleteCategory']);

/* ─── Reports ─── */
Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
 
    // Main page
    Route::get('/',          [ReportsController::class, 'index'])     ->name('index');
 
    // JSON endpoints (called by reports.js via fetch)
    Route::get('/summary',   [ReportsController::class, 'summary'])   ->name('summary');
    Route::get('/lowstock',  [ReportsController::class, 'lowstock'])  ->name('lowstock');
    Route::get('/valuation', [ReportsController::class, 'valuation']) ->name('valuation');
    Route::get('/movement',  [ReportsController::class, 'movement'])  ->name('movement');
    Route::get('/category',  [ReportsController::class, 'category'])  ->name('category');
 
    // CSV export  (GET /reports/export  or  GET /reports/export/{type})
    Route::get('/export/{type?}', [ReportsController::class, 'export'])->name('export');
 
});