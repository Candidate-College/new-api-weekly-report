<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();
            $table->integer('batch_no');
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('division');
            $table->unsignedBigInteger('supervisor_id')->nullable();
            $table->unsignedBigInteger('vice_supervisor_id')->nullable();
            $table->boolean('CFlag')->default(false);
            $table->boolean('Sflag')->default(false);
            $table->boolean('StFlag')->default(false);
            $table->string('profile_picture')->nullable();
            $table->timestamps();

            $table->foreign('supervisor_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('vice_supervisor_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
