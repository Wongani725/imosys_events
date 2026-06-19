<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sponsor_ads', function (Blueprint $table) {
            if (!Schema::hasColumn('sponsor_ads', 'file_path')) {
                $table->string('file_path', 2048)->nullable()->after('event_id');
            }
            if (!Schema::hasColumn('sponsor_ads', 'priority')) {
                $table->integer('priority')->default(0)->after('end_date');
            }
        });

        // Copy existing image data to file_path
        DB::statement("UPDATE sponsor_ads SET file_path = image WHERE file_path IS NULL AND image IS NOT NULL");

        // Drop old image column
        if (Schema::hasColumn('sponsor_ads', 'image')) {
            Schema::table('sponsor_ads', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }

    public function down()
    {
        Schema::table('sponsor_ads', function (Blueprint $table) {
            if (!Schema::hasColumn('sponsor_ads', 'image')) {
                $table->string('image', 2048)->nullable()->after('event_id');
            }
            $table->dropColumn(['file_path', 'priority']);
        });

        DB::statement("UPDATE sponsor_ads SET image = file_path WHERE image IS NULL AND file_path IS NOT NULL");
    }
};
