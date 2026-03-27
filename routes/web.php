<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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