<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\KelasController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/penilaian', function () {
    return view('penilaian.dashboard');
})->middleware(['auth', 'verified'])->name('penilaian.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/**
 * Routes untuk Kelas (API)
 */
Route::middleware('auth')->group(function () {
    Route::get('/api/kelas', [KelasController::class, 'index'])->name('api.kelas.index');
    Route::get('/api/kelas/{id}', [KelasController::class, 'show'])->name('api.kelas.show');
});

/**
 * Routes untuk Penilaian
 */
Route::middleware('auth')->group(function () {
    // GET data siswa dengan penilaian untuk DataTables
    Route::post('/penilaian/datatable', [PenilaianController::class, 'getDataTables'])->name('penilaian.datatable');
    Route::get('/penilaian/siswa/create_ajax', [PenilaianController::class, 'createSiswaAjax'])->name('penilaian.siswa.create_ajax');
    Route::post('/penilaian/siswa', [PenilaianController::class, 'storeSiswa'])->name('penilaian.siswa.store');
    Route::get('/penilaian/create_ajax', [PenilaianController::class, 'createAjax'])->name('penilaian.create_ajax');
    Route::get('/penilaian/edit_ajax/{id}', [PenilaianController::class, 'editAjax'])->name('penilaian.edit_ajax');
    
    // CRUD Penilaian
    Route::post('/penilaian', [PenilaianController::class, 'store'])->name('penilaian.store');
    Route::get('/penilaian/{id}', [PenilaianController::class, 'show'])->name('penilaian.show');
    Route::put('/penilaian/{id}', [PenilaianController::class, 'update'])->name('penilaian.update');
    Route::delete('/penilaian/{id}', [PenilaianController::class, 'destroy'])->name('penilaian.destroy');
    
    // Batch operations
    Route::post('/penilaian/batch/store', [PenilaianController::class, 'storeBatch'])->name('penilaian.batch.store');
    
    // Export
    Route::post('/penilaian/export', [PenilaianController::class, 'export'])->name('penilaian.export');
});

require __DIR__.'/auth.php';
