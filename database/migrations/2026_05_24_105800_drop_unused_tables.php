<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('occassions');
        Schema::dropIfExists('meals_owners');
        Schema::dropIfExists('event_attendants');
        Schema::dropIfExists('file22s');
        Schema::dropIfExists('files');
    }

    public function down()
    {
        // Tables would be recreated by their original migrations
    }
};
