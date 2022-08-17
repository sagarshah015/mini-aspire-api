<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiKeySecret
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     Use : check api key and secret key from env file
     */
    public function handle(Request $request, Closure $next)
    {
        if($request->header('api-key')== config('custom_vars.api_key') && $request->header('secret-key')== config('custom_vars.secret_key')){
            return $next($request);          
        }
        return response()->json(['status' =>false,'message'=> 'Wrong api-key and secret-key'],403);
    }
}
