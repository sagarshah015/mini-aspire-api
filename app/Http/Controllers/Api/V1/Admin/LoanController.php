<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserLoan;

class LoanController extends Controller
{
	/*
		use : approve loan by admin
		Return : response status with message
	*/
    function approveLoan(UserLoan $userLoan){
    	$status = true;
    	if($userLoan->status==0){
    		$userLoan->status = 1;
	    	if($userLoan->save()){
	    		$message='Loan approved successfully!';
			}else{
				$status = false;
				$message = 'Something went wrong!';
			}
    	}else if ($userLoan->status==1) {
    		$message = 'Loan has already approved';
    	}else if ($userLoan->status==2) {
    		$message = 'Loan has already paid';
    	}   	
		return $this->formatResponse($status,[],$message);
    	
    }
}
