<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home'])->name('public.home');

Route::get('/admin/login', [AuthController::class, 'create'])->name('login');
Route::post('/admin/login', [AuthController::class, 'store'])->name('admin.login.store');

Route::middleware(['auth', 'role:superadmin,admin'])->group(function () {
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/admin/logout', [AuthController::class, 'destroy'])->name('admin.logout');
});
