<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKpiDivisionTable extends Migration
{
    public function up(): void
    {
        Schema::create('division_kpis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('division_id');
            $table->year('year');
            $table->string('task_name');
            $table->string('task_id');
            $table->timestamps();

            $table->foreign('division_id')->references('id')->on('divisions');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_division');
    }
}
