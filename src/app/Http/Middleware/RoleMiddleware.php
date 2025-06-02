<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $role = null)
    {
        if(!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $path = $request->path();

        if($role !== null && $user->role != $role) {
            abort(403, 'アクセス権がありません');
        }

        if($user->role === 0 && $path === '/admin/login') {
            return back()->withErrors(['管理者ユーザとしての権限が必要です']);
        }
        return $next($request);
    }
}
