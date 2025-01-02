<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKpiRatingsTable extends Migration
{
    public function up()
    {
        Schema::create('kpis', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->year('year');
            $table->tinyInteger('month');
            $table->float('activeness_Q1_realization')->nullable();
            $table->float('activeness_Q2_realization')->nullable();
            $table->float('activeness_Q3_realization')->nullable();
            $table->float('ability_Q1_realization')->nullable();
            $table->float('communication_Q1_realization')->nullable();
            $table->float('communication_Q2_realization')->nullable();
            $table->float('discipline_Q1_realization')->nullable();
            $table->float('discipline_Q2_realization')->nullable();
            $table->float('discipline_Q3_realization')->nullable();
            $table->timestamps();

            $table->primary(['user_id', 'year', 'month']);
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kpis');
    }
}
