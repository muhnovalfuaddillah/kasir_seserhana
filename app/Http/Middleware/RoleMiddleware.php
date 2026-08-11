<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        return redirect()->back()->with('error', 'Akses Ditolak! Fitur ini khusus untuk role ' . implode('/', $roles));
    }
}
