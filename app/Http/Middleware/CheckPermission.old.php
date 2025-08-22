<?php

namespace App\Http\Middleware;

use Closure;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $permission = array_slice(func_get_args(), 2);
        if (auth()->check() && auth()->user()->can($permission))
        {
            return $next($request);
        }
        return redirect('/');
        
    }
}
