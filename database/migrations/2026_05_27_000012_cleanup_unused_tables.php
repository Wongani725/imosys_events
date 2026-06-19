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
        Schema::dropIfExists('attire_colors');
        Schema::dropIfExists('attire_types');

        if (Schema::hasTable('booking_forms')) {
            $count = DB::table('booking_forms')->count();
            if ($count === 0) {
                Schema::dropIfExists('booking_forms');
            }
        }
    }

    public function down()
    {
    }
};
