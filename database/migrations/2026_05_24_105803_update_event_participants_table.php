<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('event_participants', function (Blueprint $table) {
            $drop = [];
            if (Schema::hasColumn('event_participants', 'attire_type')) $drop[] = 'attire_type';
            if (Schema::hasColumn('event_participants', 'attire_size')) $drop[] = 'attire_size';
            if (!empty($drop)) $table->dropColumn($drop);
        });
        
        if (!Schema::hasColumn('event_participants', 'hotel_id')) {
            Schema::table('event_participants', function (Blueprint $table) {
                $table->unsignedBigInteger('hotel_id')->nullable()->after('company_name');
            });
        }
        if (!Schema::hasColumn('event_participants', 'room_type_id')) {
            Schema::table('event_participants', function (Blueprint $table) {
                $table->unsignedBigInteger('room_type_id')->nullable()->after('hotel_id');
            });
        }
        if (!Schema::hasColumn('event_participants', 'room_allocated')) {
            Schema::table('event_participants', function (Blueprint $table) {
                $table->string('room_allocated', 100)->nullable()->after('room_type_id');
            });
        }
        if (!Schema::hasColumn('event_participants', 'accommodation')) {
            Schema::table('event_participants', function (Blueprint $table) {
                $table->boolean('accommodation')->default(false)->after('room_allocated');
            });
        }
        if (!Schema::hasColumn('event_participants', 'event_selection')) {
            Schema::table('event_participants', function (Blueprint $table) {
                $table->enum('event_selection', ['governance', 'main'])->default('main')->after('accommodation');
            });
        }
    }

    public function down()
    {
        Schema::table('event_participants', function (Blueprint $table) {
            $table->dropColumn([
                'hotel_id', 'room_type_id', 'room_allocated', 'accommodation', 'event_selection'
            ]);
            $table->string('attire_type')->nullable();
            $table->string('attire_size')->nullable();
        });
    }
};
