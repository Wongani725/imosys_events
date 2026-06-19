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
            if (Schema::hasColumn('event_participants', 'room_allocated')) $drop[] = 'room_allocated';
            if (Schema::hasColumn('event_participants', 'attire_type')) $drop[] = 'attire_type';
            if (Schema::hasColumn('event_participants', 'attire_size')) $drop[] = 'attire_size';
            if (!empty($drop)) $table->dropColumn($drop);

            if (!Schema::hasColumn('event_participants', 'spouse_name')) {
                $table->string('spouse_name')->nullable()->after('event_selection');
            }
            if (!Schema::hasColumn('event_participants', 'extras_count')) {
                $table->integer('extras_count')->default(0)->after('spouse_name');
            }
            if (!Schema::hasColumn('event_participants', 'booker_id')) {
                $table->unsignedBigInteger('booker_id')->nullable()->after('extras_count');
                $table->foreign('booker_id')->references('bookingID')->on('bookers')->onDelete('set null');
            }
            if (!Schema::hasColumn('event_participants', 'is_walkin')) {
                $table->boolean('is_walkin')->default(false)->after('booker_id');
            }
            if (!Schema::hasColumn('event_participants', 'walkin_added_by')) {
                $table->unsignedBigInteger('walkin_added_by')->nullable()->after('is_walkin');
                $table->foreign('walkin_added_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('event_participants', function (Blueprint $table) {
            $table->dropForeign(['booker_id']);
            $table->dropForeign(['walkin_added_by']);
            $table->dropColumn(['spouse_name', 'extras_count', 'booker_id', 'is_walkin', 'walkin_added_by']);
        });
    }
};
