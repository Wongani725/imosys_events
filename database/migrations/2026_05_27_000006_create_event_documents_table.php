<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('event_documents')) {
            Schema::create('event_documents', function (Blueprint $table) {
                $table->id();
                $table->string('event_id', 250);
                $table->string('title');
                $table->string('file_path', 2048);
                $table->string('type')->default('document');
                $table->boolean('is_public')->default(false);
                $table->timestamps();

                $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('event_documents');
    }
};
