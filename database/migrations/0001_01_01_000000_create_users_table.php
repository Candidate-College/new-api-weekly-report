<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id()->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('instagram');
            $table->string('linkedin');
            $table->string('batch_no');
            $table->string('hashed_password');
            $table->timestamp('email_verified_at');
            $table->string('division');
            $table->unsignedBigInteger('supervisor_id')->nullable();
            $table->boolean('CFlag')->default(false);
            $table->boolean('SFlag')->default(false);
            $table->boolean('StFlag')->default(false);
            $table->string('profile_picture');
            $table->timestamps();

            $table->foreign('supervisor_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
