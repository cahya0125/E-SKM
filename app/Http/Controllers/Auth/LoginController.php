<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class LoginController extends Controller
{
    /**
     * Menampilkan form login admin.
     */
    public function create()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Memproses percobaan login.
     * Validasi + logika Auth::attempt sudah dipindah ke LoginRequest::authenticate().
     */
    public function store(LoginRequest $request)
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        $request->authenticate();

        $request->session()->regenerate();

        $request->user()->forceFill(['last_login_at' => Carbon::now()])->save();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Logout admin.
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}