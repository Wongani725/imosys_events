<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('master_meal_tags', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 250);
            $table->unsignedBigInteger('member_id');
            $table->integer('total_meals');
            $table->string('unique_code')->unique();
            $table->string('qrcode_path', 2048)->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('event_id')->references('event_id')->on('events');
            $table->foreign('member_id')->references('id')->on('members');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_meal_tags');
    }
};
