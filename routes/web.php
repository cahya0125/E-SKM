<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Survey\HomeController;


// ============================= 
//         Survey Masyarakat
// =============================
Route::get('/', [HomeController::class, 'index'])->name('home');
