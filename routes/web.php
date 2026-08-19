<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Survey\HomeController;
use App\Http\Controllers\Auth\LoginController;


// ============================= 
//         Survey Masyarakat
// =============================
Route::get('/', [HomeController::class, 'index'])->name('home');


// Login admin
Route::get('/admin/login', [LoginController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'store'])->name('admin.login.attempt');
 
// ==== Area admin (wajib login + role admin + status aktif) ====
Route::middleware('admin')->prefix('admin')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('admin.logout');
 
    // Placeholder — ganti dengan controller dashboard asli setelah Modul 2 selesai
    Route::get('/dashboard', fn () => 'Dashboard admin (belum dibuat)')->name('admin.dashboard');
});