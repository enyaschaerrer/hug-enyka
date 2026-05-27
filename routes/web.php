<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CollectionController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\CoBrandedCollecteController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home'])->name('public.home');
Route::get('/collecte', fn () => view('app'))->name('public.collecte');
Route::get('/trophee', fn () => view('app'))->name('public.trophy');
Route::get('/label', fn () => view('app'))->name('public.label');
Route::get('/contact', fn () => view('app'))->name('public.contact');
Route::get('/collecte/{brand}/{token}', [CoBrandedCollecteController::class, 'show'])->name('public.collecte.cobranded');

// Admin SPA shell — login page is public
Route::get('/admin/login', fn () => view('app'))->name('login');
Route::post('/admin/login', [AuthController::class, 'store'])->name('admin.login.store');

// Admin SPA shell + JSON actions — require auth + role
Route::middleware(['auth', 'role:superadmin,admin'])->group(function () {
    Route::post('/admin/logout', [AuthController::class, 'destroy'])->name('admin.logout');
    Route::get('/admin/api/companies', [CompanyController::class, 'index'])->name('admin.companies.index');
    Route::get('/admin/api/companies/{company}', [CompanyController::class, 'show'])->name('admin.companies.show');
    Route::post('/admin/companies', [CompanyController::class, 'store'])->name('admin.companies.store');
    Route::patch('/admin/companies/{company}', [CompanyController::class, 'update'])->name('admin.companies.update');
    Route::delete('/admin/companies/{company}', [CompanyController::class, 'destroy'])->name('admin.companies.destroy');
    Route::post('/admin/companies/{company}/collections', [CollectionController::class, 'store'])->name('admin.collections.store');
    Route::patch('/admin/companies/{company}/collections/{collection}', [CollectionController::class, 'update'])->name('admin.collections.update');

    Route::get('/admin/{any?}', fn () => view('app'))
        ->where('any', '.*')
        ->name('admin.shell');
});

Route::fallback(fn () => redirect('/'));
