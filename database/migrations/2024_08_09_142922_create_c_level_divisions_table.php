<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCLevelDivisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('c_level_divisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('c_level_id')->constrained()->onDelete('cascade'); // Foreign key to CLevel
            $table->foreignId('division_id')->constrained()->onDelete('cascade'); // Foreign key to Division
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
        Schema::dropIfExists('c_level_divisions');
    }
}
