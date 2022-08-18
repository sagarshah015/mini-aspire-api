<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /*
        Use : authentic user with email and password
        Return : status with token
    */
    public function authenticate(){
        $credentials = request(['email', 'password']);
        if (!$token = auth()->guard('admins')->attempt($credentials)) {
            return $this->formatResponse(false,[],'Login credentials are invalid.',401);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->formatResponse(true,[],'Login successfully.')->header('token',$token);
    } 

   
}
