<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthCheck
{
    /**
     * Memastikan user sudah login (ada session 'user').
     * Jika belum login, redirect ke /login.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('user')) {
            return redirect('/login')
                ->withErrors(['login' => 'Silakan login terlebih dahulu.']);
        }
        return $next($request);
    }
}
