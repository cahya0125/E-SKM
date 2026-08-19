<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Survey\HomeController;


// ============================= 
//         Survey Masyarakat
// =============================
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');
Route::view('/admin/users', 'admin.users')->name('admin.users');
