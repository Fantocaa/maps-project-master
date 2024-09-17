<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {

                $user = Auth::user();

                if ($request->path() == 'register' && $user->hasRole('superadmin')) {
                    return $next($request);
                }
                if ($user->hasRole('admin')) {
                    return redirect('/maps/admin');
                } else if ($user->hasRole('superuser')) {
                    return redirect('/maps/superuser');
                } else if ($user->hasRole('superuser2')) {
                    return redirect('/maps/superuser');
                } else if ($user->hasRole('user')) {
                    return redirect('/maps/user');
                } else
                    return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
