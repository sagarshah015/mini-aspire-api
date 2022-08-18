<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /*
        Use : authentic user with email and password
        Return : status with token
    */
    public function authenticate(Request $request){
        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return $this->formatResponse(false,$validator->messages(),$this->validationErrorMessage,422);
        }

        //Request is validated
        //Crean token
        try {
            if (! $token = auth()->guard('users')->attempt($credentials)) {
                return $this->formatResponse(false,[],'Login credentials are invalid.',401);
            }
        } catch (JWTException $e) {
            return $this->formatResponse(false,[],'Could not create token.',500);
        }
 	
 		//Token created, return with success response and jwt token
        return $this->formatResponse(true,[],'Login successfully.')->header('token',$token);
    }
}