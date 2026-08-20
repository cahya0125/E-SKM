<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Lindungi semua route dashboard admin.
     *
     * Ditolak (redirect ke form login + pesan) kalau:
     * - belum login
     * - role-nya bukan admin/superadmin
     * - akunnya berstatus nonaktif
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()
                ->route('admin.login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        if ($user->role !== 'admin' || $user->status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}