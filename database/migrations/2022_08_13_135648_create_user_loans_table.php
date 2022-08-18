<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_loans', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('userId');
            $table->string('name',30);
            $table->foreign('userId')->references('id')->on('users');
            $table->double('amount', 12, 4);
            $table->double('dueAmount', 12, 4);
            $table->integer('terms');
            $table->tinyInteger('status')->default(0)->comment('0- Pending, 1- Approved, 2- Paid');
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
        Schema::dropIfExists('user_loans');
    }
}
