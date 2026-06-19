<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('one_time_passwords')) {
            Schema::create('one_time_passwords', function (Blueprint $table) {
                $table->id();
                $table->string('code', 10);
                $table->string('purpose')->default('account creation');
                $table->string('channel')->default('email');
                $table->string('status')->default('pending');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->text('payload')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('one_time_passwords');
    }
};
