<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonthlyFeedbacksTable extends Migration
{
    public function up()
    {
        Schema::create('monthly_feedbacks', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->year('year');
            $table->tinyInteger('month');
            $table->text('content_text');
            $table->timestamps();

            $table->primary(['user_id', 'year', 'month']);
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('monthly_feedbacks');
    }
}
