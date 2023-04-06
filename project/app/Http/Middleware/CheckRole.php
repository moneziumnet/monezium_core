<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $data)
    {

        if (Auth::guard('admin')->check()) {
            if(($data === 'admin' && Auth::guard('admin')->user()->role === 'admin') || $data === 'staff') {
                return $next($request);
            }
        }
        return redirect()->route('admin.dashboard')->with('unsuccess',"You don't have access to that section");
    }
}
