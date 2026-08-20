<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Survey\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PengunaController;
use App\Http\Controllers\Admin\RespondensController;


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

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/users', [PengunaController::class, 'index'])->name('admin.users');
    Route::post('/users', [PengunaController::class, 'store'])->name('admin.users.store');
    Route::patch('/users/{user}', [PengunaController::class, 'update'])->name('admin.users.update');
    Route::post('/users/{user}/reset-password', [PengunaController::class, 'resetPassword'])->name('admin.users.reset-password');
    Route::delete('/users/{user}', [PengunaController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/respondens', [RespondensController::class, 'index'])->name('admin.respondens');
    Route::post('/respondens', [RespondensController::class, 'store'])->name('admin.respondens.store');
    Route::patch('/respondens/{respondens}', [RespondensController::class, 'update'])->name('admin.respondens.update');
    Route::delete('/respondens/{respondens}', [RespondensController::class, 'destroy'])->name('admin.respondens.destroy');
});
