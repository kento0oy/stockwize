<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthManager
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('manager_logged_in')) {
            return redirect('/')->with('error', 'Please log in to continue.');
        }

        return $next($request);
    }
}