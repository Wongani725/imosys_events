<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("DROP VIEW IF EXISTS upcoming_events");
        DB::statement("
            CREATE VIEW upcoming_events AS
            SELECT
                e.event_name,
                e.event_id,
                s.session_id,
                s.description AS session_description,
                s.start_time AS adjusted_start_time,
                s.session_date,
                s.start_time,
                s.end_time
            FROM events e
            INNER JOIN event_sessions s ON e.event_id = s.event_id
            WHERE e.event_status = 'active'
              AND e.end_date >= CURDATE()
              AND (
                s.session_date > CURDATE()
                OR (s.session_date = CURDATE() AND s.end_time > CURTIME())
              )
            ORDER BY s.session_date ASC, s.start_time ASC
            LIMIT 1
        ");
    }

    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS upcoming_events");
        DB::statement("
            CREATE VIEW upcoming_events AS
            SELECT
                e.event_name,
                e.event_id,
                s.session_id,
                s.description AS session_description,
                s.start_time AS adjusted_start_time,
                s.session_date,
                s.start_time,
                s.end_time
            FROM events e
            INNER JOIN event_sessions s ON e.event_id = s.event_id
            WHERE e.event_status = 'active'
              AND e.end_date >= CURDATE()
            ORDER BY s.session_date ASC, s.start_time ASC
        ");
    }
};
