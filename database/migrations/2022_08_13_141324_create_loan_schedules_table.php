<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('userLoanId');
            $table->foreign('userLoanId')->references('id')->on('user_loans');
            $table->double('amount', 8, 4);
            $table->integer('term');
            $table->date('scheduleDate');            
            $table->boolean('isPaid')->default(0)->comment('0- Pending, 1- Paid');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_loan_schedules');
    }
}
