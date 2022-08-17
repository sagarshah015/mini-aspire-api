<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role = null)
    {
        try {
            $token_role = JWTAuth::parseToken($request->header('token'))->getClaim('role');
            JWTAuth::parseToken($request->header('token'))->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(['status' =>false,'message' => 'Token is Invalid'],403);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json(['status' =>false,'message' => 'Token is Expired'],403);
            }else{
                return response()->json(['status' =>false,'message'=> 'Authorization Token not found'],403);
            }
        }
        if ($token_role != $role) {
           return response()->json(['status' =>false,'message'=> 'Token is Invalid'],403);
        }
        return $next($request);
    }
}