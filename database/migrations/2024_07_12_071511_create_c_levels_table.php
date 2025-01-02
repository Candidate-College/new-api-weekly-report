<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('c_levels', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name'); // Name of the position
            $table->string('abbreviation')->nullable(); // Abbreviation column
            $table->string('responsibility')->nullable();
            $table->string('description')->nullable();
            $table->timestamps(); // Created at and Updated at columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('c_levels');
    }
}
