<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }
        if (auth()->user()->isAdmin()) {
            return redirect('/admin/attendance/list');
        }
        return $next($request);
    }
}
