<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Survey\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PengunaController;
use App\Http\Controllers\Admin\RespondensController;
use App\Http\Controllers\Admin\GrafikController;
use App\Http\Controllers\Admin\SaranKritiksController;
use App\Http\Controllers\Admin\UnsurPelayananController;
use App\Http\Controllers\Admin\HasilIkmController;
use App\Http\Controllers\Laporan\LaporanController;
use App\Http\Controllers\Survey\SurveyController;

// ============================= 
//         Survey Masyarakat
// =============================
Route::get('/', [HomeController::class, 'index'])->name('home');

// Login
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [LoginController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [LoginController::class, 'store'])->name('admin.login.attempt');
});

// ==== Area admin (wajib login + role admin/petugas + status aktif) ====
Route::middleware('admin')->prefix('admin')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('admin.logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::middleware('admin.only')->group(function () {
        Route::get('/users', [PengunaController::class, 'index'])->name('admin.users');
        Route::post('/users', [PengunaController::class, 'store'])->name('admin.users.store');
        Route::patch('/users/{user}', [PengunaController::class, 'update'])->name('admin.users.update');
        Route::post('/users/{user}/reset-password', [PengunaController::class, 'resetPassword'])->name('admin.users.reset-password');
        Route::delete('/users/{user}', [PengunaController::class, 'destroy'])->name('admin.users.destroy');
    });

    Route::get('/respondens', [RespondensController::class, 'index'])->name('admin.respondens');
    Route::post('/respondens', [RespondensController::class, 'store'])->name('admin.respondens.store');
    Route::patch('/respondens/{respondens}', [RespondensController::class, 'update'])->name('admin.respondens.update');
    Route::delete('/respondens/{respondens}', [RespondensController::class, 'destroy'])->name('admin.respondens.destroy');
    Route::get('/grafik', [GrafikController::class, 'index'])->name('admin.grafik');

    Route::get('/kritik-saran', [SaranKritiksController::class, 'index'])->name('admin.kritik-saran');
    Route::patch('/saran-kritik/{saranKritik}/status', [SaranKritiksController::class, 'updateStatus'])->name('admin.kritik-saran.update-status');
    Route::delete('/saran-kritik/{saranKritik}', [SaranKritiksController::class, 'destroy'])->name('admin.kritik-saran.destroy');

    Route::get('/unsur-pelayanan', [UnsurPelayananController::class, 'index'])->name('admin.unsur-pelayanan');
    Route::post('/unsur-pelayanan', [UnsurPelayananController::class, 'store'])->name('admin.unsur-pelayanan.store');
    Route::patch('/unsur-pelayanan/{unsurPelayanan}', [UnsurPelayananController::class, 'update'])->name('admin.unsur-pelayanan.update');
    Route::delete('/unsur-pelayanan/{unsurPelayanan}', [UnsurPelayananController::class, 'destroy'])->name('admin.unsur-pelayanan.destroy');

    Route::get('/hasil-ikm', [HasilIkmController::class, 'index'])->name('admin.hasil-ikm');
    Route::post('/hasil-ikm/hitung-ulang', [HasilIkmController::class, 'hitungUlang'])->name('admin.hasil-ikm.hitung-ulang');
    Route::get('/hasil-ikm/pdf', [HasilIkmController::class, 'downloadPdf'])->name('admin.hasil-ikm.pdf');

    Route::get('/laporan', [LaporanController::class, 'index'])->name('admin.laporan');
    Route::post('/laporan/pdf', [LaporanController::class, 'exportPdf'])->name('admin.laporan.pdf');
    Route::post('/laporan/word', [LaporanController::class, 'exportWord'])->name('admin.laporan.word');
    Route::post('/laporan/excel', [LaporanController::class, 'exportExcel'])->name('admin.laporan.excel');
});

//Survey
Route::prefix('survei')->name('survey.')->controller(SurveyController::class)->group(function () {
    Route::get('/mulai', 'mulai')->name('mulai');                    // halaman "Mulai Survei"
    Route::post('/mulai', 'start')->name('start');
    Route::get('/responden', 'responden')->name('responden');
    Route::post('/responden', 'saveResponden')->name('responden.save');
    Route::get('/penilaian', 'penilaian')->name('penilaian');
    Route::post('/penilaian', 'savePenilaian')->name('penilaian.save');
    Route::get('/saran', 'saran')->name('saran');
    Route::post('/saran', 'saveSaran')->name('saran.save');
    Route::get('/review', 'review')->name('review');
    Route::post('/kirim', 'submit')->name('submit');
    Route::get('/selesai', 'selesai')->name('selesai');
});

// ============ TESTING HALAMAN ERROR (hanya environment local) ============
if (app()->environment(['local', 'development'])) {
    Route::prefix('test-error')->group(function () {
        Route::get('/401', fn () => abort(401));
        Route::get('/403', fn () => abort(403));
        Route::get('/404', fn () => abort(404));
        Route::get('/419', fn () => abort(419));
        Route::get('/429', fn () => abort(429));
        Route::get('/500', fn () => abort(500));
    });
}