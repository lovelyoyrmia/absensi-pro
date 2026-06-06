<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OwnerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role === 'owner') {
            return $next($request);
        }
        abort(403, 'Akses ditolak. Halaman ini hanya untuk Owner/Pemilik sistem.');
    }
}