<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\CoBrandedCollecteController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home'])->name('public.home');
Route::get('/collecte', [PublicSiteController::class, 'home'])->name('public.collecte');
Route::get('/trophee', [PublicSiteController::class, 'home'])->name('public.trophy');
Route::get('/label', [PublicSiteController::class, 'home'])->name('public.label');
Route::get('/contact', [PublicSiteController::class, 'home'])->name('public.contact');
Route::get('/collecte/{brand}/{token}', [CoBrandedCollecteController::class, 'show'])->name('public.collecte.cobranded');

// Admin SPA shell — login page is public
Route::get('/admin/login', fn () => view('app'))->name('login');
Route::post('/admin/login', [AuthController::class, 'store'])->name('admin.login.store');

// Admin SPA shell + JSON actions — require auth + role
Route::middleware(['auth', 'role:superadmin,admin'])->group(function () {
    Route::post('/admin/logout', [AuthController::class, 'destroy'])->name('admin.logout');
    Route::post('/admin/companies', [CompanyController::class, 'store'])->name('admin.companies.store');

    Route::get('/admin/{any?}', fn () => view('app'))
        ->where('any', '.*')
        ->name('admin.shell');
});

Route::fallback(fn () => redirect('/'));
