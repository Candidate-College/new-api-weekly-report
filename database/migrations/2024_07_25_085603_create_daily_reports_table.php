<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyReportsTable extends Migration
{
    public function up()
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at');
            $table->text('content_text');
            $table->string('content_photo')->nullable();
            $table->timestamp('last_updated_at');

            $table->primary(['user_id', 'created_at']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_reports');
    }
}
