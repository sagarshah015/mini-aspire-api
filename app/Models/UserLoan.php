<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoan extends Model
{
    use HasFactory;
    protected $fillable = [
        'userId',
        'name',
        'amount',
        'balanceAmount',
        'terms',
    ];

    // relation with loan_schedules for get all schedule loans
    function loan_schedules(){
        return $this->hasMany(LoanSchedule::class, 'userLoanId', 'id');
    }
}
