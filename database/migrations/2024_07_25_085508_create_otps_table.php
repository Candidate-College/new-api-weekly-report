<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpsTable extends Migration
{
    public function up()
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at');
            $table->timestamp('expiration_time')->nullable();
            $table->string('OTP_code', 4);
            $table->string('token');

            $table->primary(['user_id', 'created_at']);
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('otps');
    }
}
