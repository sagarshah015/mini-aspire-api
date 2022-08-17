<?php
	return [
	    'api_key' => env('API_KEY'),
	    'secret_key' => env('SECRET_KEY'),
	    'currency' => '$',
	    'loan_payment_frequency' => '7',
	    'max_loan_amount' => 300000,
	    'max_loan_terms' => 52 // max loan terms should be 52 weeks/
	];