<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_id');
            $table->string('name', 100);
            $table->integer('quantity')->default(0);
            $table->integer('available_count')->default(0);
            $table->integer('booked_count')->default(0);
            $table->timestamps();
            
            $table->foreign('hotel_id')->references('id')->on('hotel')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('room_types');
    }
};
