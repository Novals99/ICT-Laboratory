<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSpv
{
    /**
     * Handle an incoming request.
     * Hanya izinkan user dengan kolom role === 'spv inventory'.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'spv inventory') {
            abort(403, 'Akses terbatas: hanya SPV Inventory yang boleh mengakses halaman ini.');
        }

        return $next($request);
    }
}
