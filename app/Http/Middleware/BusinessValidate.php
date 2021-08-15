<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class BusinessValidate
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
        // dd(Auth::guard('api')->user()->getRoleNames()[0] == 'business'); 
        if(Auth::guard('api')->user()->getRoleNames()[0] == 'business'){
            return $next($request);
        }
        else
        {
            return response('Unauthorized. You are not access this route , change your role.', 401);
        }
    }
}
