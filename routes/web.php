<?php

use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'index'])->name('home');
Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');
Route::get('/sites/create', [SiteController::class, 'create'])->name('sites.create');
Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');

Route::prefix('sites/{site}')->group(function () {
    Route::get('/', [SiteController::class, 'edit'])->name('sites.edit');
    Route::post('/generate', [SiteController::class, 'generate'])->name('sites.generate');
    Route::post('/publish', [SiteController::class, 'publish'])->name('sites.publish');
    Route::post('/unpublish', [SiteController::class, 'unpublish'])->name('sites.unpublish');
    Route::put('/sections/{section}', [SiteController::class, 'updateSection'])->name('sections.update');
    Route::post('/sections/{section}/regenerate', [SiteController::class, 'regenerateSection'])->name('sections.regenerate');
});

Route::get('/s/{slug}', [PublicSiteController::class, 'show'])->name('public.site');
