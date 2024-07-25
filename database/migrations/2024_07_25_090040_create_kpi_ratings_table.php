<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kpi_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->smallInteger('year');
            $table->tinyInteger('month'); 
            $table->float('activeness_Q1_score');
            $table->float('activeness_Q2_score');
            $table->float('activeness_Q3_score');
            $table->float('ability_Q1_score');
            $table->float('communication_Q1_score');
            $table->float('communication_Q2_score');
            $table->float('discipline_Q1_score');
            $table->float('discipline_Q2_score');
            $table->float('discipline_Q3_score');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_ratings');
    }
};
