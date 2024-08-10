<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCLevelDivisionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('c_level_divisions', function (Blueprint $table) {
            $table->unsignedBigInteger('c_level_id');
            $table->unsignedBigInteger('division_id');
            $table->timestamps();

            $table->foreign('c_level_id')->references('id')->on('users');
            $table->foreign('division_id')->references('id')->on('divisions');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('c_level_divisions');
    }
}
