<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScrapingController;
use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Dashboard routes
Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/mahasiswa', [DashboardController::class, 'mahasiswa'])->name('mahasiswa');
    Route::get('/mata-kuliah', [DashboardController::class, 'mataKuliah'])->name('mata-kuliah');
    Route::get('/nilai', [DashboardController::class, 'nilai'])->name('nilai');
    Route::get('/mahasiswa/{id}', [DashboardController::class, 'detailMahasiswa'])->name('detail-mahasiswa');
    Route::get('/mata-kuliah/{id}', [DashboardController::class, 'detailMataKuliah'])->name('detail-mata-kuliah');
});

// Scraping routes
Route::prefix('scraping')->name('scraping.')->group(function () {
    Route::get('/', [ScrapingController::class, 'index'])->name('index');
    Route::post('/login', [ScrapingController::class, 'login'])->name('login');
    Route::get('/check-session', [ScrapingController::class, 'checkSession'])->name('check-session');
    Route::get('/semesters', [ScrapingController::class, 'getSemesters'])->name('semesters');
    Route::post('/scrape-nilai', [ScrapingController::class, 'scrapeNilai'])->name('scrape-nilai');
    Route::post('/scrape-mahasiswa', [ScrapingController::class, 'scrapeMahasiswa'])->name('scrape-mahasiswa');
    Route::get('/progress', [ScrapingController::class, 'getScrapingProgress'])->name('progress');
});

// Export routes
Route::prefix('export')->name('export.')->group(function () {
    Route::get('/mahasiswa/{format}', [ExportController::class, 'mahasiswa'])->name('mahasiswa');
    Route::get('/mata-kuliah/{format}', [ExportController::class, 'mataKuliah'])->name('mata-kuliah');
    Route::get('/nilai/{format}', [ExportController::class, 'nilai'])->name('nilai');
});
