<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScrapingController;
use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard.index')
        : redirect()->route('login.show');
});

// Auth routes
Route::get('login', [AuthController::class, 'showLogin'])->name('login.show');
Route::post('login', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/mahasiswa', [DashboardController::class, 'mahasiswa'])->name('mahasiswa');
        Route::get('/mata-kuliah', [DashboardController::class, 'mataKuliah'])->name('mata-kuliah');
        Route::get('/nilai', [DashboardController::class, 'nilai'])->name('nilai');
        Route::get('/mahasiswa/{id}', [DashboardController::class, 'detailMahasiswa'])
            ->whereNumber('id')
            ->name('detail-mahasiswa');
        Route::get('/mata-kuliah/{id}', [DashboardController::class, 'detailMataKuliah'])
            ->whereNumber('id')
            ->name('detail-mata-kuliah');
    });

    // Scraping
    Route::prefix('scraping')->name('scraping.')->group(function () {
        Route::get('/', [ScrapingController::class, 'index'])->name('index');
        Route::get('/status', [ScrapingController::class, 'status'])->name('status');
        Route::post('/login', [ScrapingController::class, 'login'])->name('login');
        Route::get('/check-session', [ScrapingController::class, 'checkSession'])->name('check-session');
        Route::get('/semesters', [ScrapingController::class, 'getSemesters'])->name('semesters');
        Route::post('/scrape-nilai', [ScrapingController::class, 'scrapeNilai'])->name('scrape-nilai');
        Route::post('/scrape-mahasiswa', [ScrapingController::class, 'scrapeMahasiswa'])->name('scrape-mahasiswa');
        Route::get('/progress', [ScrapingController::class, 'getScrapingProgress'])->name('progress');
        Route::get('/job-progress', [ScrapingController::class, 'getJobProgress'])->name('job-progress');
        Route::get('/batch-progress', [ScrapingController::class, 'getBatchProgress'])->name('batch-progress');
        Route::post('/batch-scraping', [ScrapingController::class, 'startBatchScraping'])->name('batch-scraping');
        Route::get('/active-jobs', [ScrapingController::class, 'getActiveJobs'])->name('active-jobs');
    });

    // Export
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/mahasiswa/{format}', [ExportController::class, 'mahasiswa'])
            ->where('format', 'excel|json|pdf')
            ->name('mahasiswa');
        Route::get('/mata-kuliah/{format}', [ExportController::class, 'mataKuliah'])
            ->where('format', 'excel|json|pdf')
            ->name('mata-kuliah');
        Route::get('/nilai/{format}', [ExportController::class, 'nilai'])
            ->where('format', 'excel|json|pdf')
            ->name('nilai');
    });
});
