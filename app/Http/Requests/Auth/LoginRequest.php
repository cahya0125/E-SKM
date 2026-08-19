<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Semua orang boleh membuka form login.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi input.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Coba autentikasi user berdasarkan username & password.
     *
     * Menangani 3 kondisi:
     * 1. Terlalu banyak percobaan gagal -> lockout sementara.
     * 2. Username/password salah -> pesan error umum (tidak bocorkan mana yang salah).
     * 3. Akun ditemukan tapi statusnya nonaktif -> pesan khusus.
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('username', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'username' => 'Username atau password salah.',
            ]);
        }

        // Login berhasil secara kredensial, tapi cek status akunnya masih aktif.
        if (Auth::user()?->status !== 'active') {
            Auth::logout();

            throw ValidationException::withMessages([
                'username' => 'Akun Anda telah dinonaktifkan. Hubungi administrator sistem.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Pastikan request belum melebihi batas percobaan login (5 kali).
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    /**
     * Key unik untuk rate limiting — kombinasi username + alamat IP,
     * supaya user lain di IP yang sama tidak ikut kena limit.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('username')).'|'.$this->ip());
    }
}