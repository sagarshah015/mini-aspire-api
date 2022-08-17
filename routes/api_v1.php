<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Customer\AuthController;
use App\Http\Controllers\Api\V1\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\V1\Admin\LoanController as AdminLoanController;
use App\Http\Controllers\Api\V1\Customer\LoanController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// ================= Admin routes ============================

Route::group(['prefix' => 'admin'],function(){
	Route::post('login', [AdminAuthController::class, 'authenticate']);
	Route::group(['middleware' => ['jwt.verify:admin']],function (){
		Route::patch('approve-loan/{userLoan}',[AdminLoanController::class, 'approveLoan']);	
	});
});

// ======================== customer routes =======================

Route::group(['prefix' => 'customer'],function(){
	Route::post('/login', [AuthController::class, 'authenticate']);

	Route::group(['middleware' => ['jwt.verify:user']],function (){
		Route::post('loan-create', [LoanController::class, 'loanCreate']);	
		Route::post('loan-repayment/{userLoan}', [LoanController::class, 'loanRepayment']);
		Route::get('view-loans', [LoanController::class, 'viewLoans']);
	});
});

Route::fallback(function(){
    return response()->json(['message' => 'Not Found.'], 404);
})->name('api.fallback.404');

