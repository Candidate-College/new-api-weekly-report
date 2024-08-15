<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKpiDivisionTable extends Migration
{
    public function up(): void
    {
        Schema::create('division_kpis', function (Blueprint $table) {
            $table->unsignedBigInteger('division_id');
            $table->year('year');
            $table->tinyInteger('month');
            $table->string('task_name');
            $table->float('weight');
            $table->float('target');
            $table->float('end_of_month_realization')->nullable();
            $table->timestamps();

            $table->foreign('division_id')->references('id')->on('divisions');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_division');
    }
}
