<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    private $statusCode = 200;
    protected $validationErrorMessage = 'Validation fails';
    protected $exceptionErrorMessage = 'Something went wrong!';

    protected function formatResponse($success,$data,$message,$status=200){
    	$response = [
            'success' => $success,
            'data' => $data,
            'message' => $message,
        ];

        return response()->json($response, $status)
        		->withHeaders(['X-content-Type-Options'=>'nosniiff',
		        	'Cache-Control'=>'no-store,no-cache,must-revalidate',
		        	'pragma'=>'no-cache',
		        	'X-Frame_optiona'=>'sameorigin']);
    }
}
