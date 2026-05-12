<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('
            CREATE PROCEDURE show_appointments()
            BEGIN
                SELECT a.id, CONCAT(c.first_name, " ", c.last_name) as client_name, a.service_type, a.appointment_date, a.appointment_time, a.status
                FROM appointments a
                JOIN clients c ON a.client_id = c.id;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS show_appointments');
    }
};
