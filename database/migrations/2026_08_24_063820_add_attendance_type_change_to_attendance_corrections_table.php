<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_corrections', function (Blueprint $table) {
            $table->string('old_attendance_type', 50)
                ->nullable()
                ->after('new_attendance_date');

            $table->string('new_attendance_type', 50)
                ->nullable()
                ->after('old_attendance_type');

            $table->string('old_work_session_name', 150)
                ->nullable()
                ->after('new_attendance_type');

            $table->string('new_work_session_name', 150)
                ->nullable()
                ->after('old_work_session_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_corrections', function (Blueprint $table) {
            $table->dropColumn([
                'old_attendance_type',
                'new_attendance_type',
                'old_work_session_name',
                'new_work_session_name',
            ]);
        });
    }
};