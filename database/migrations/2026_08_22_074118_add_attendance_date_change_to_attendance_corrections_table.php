<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_corrections', function (Blueprint $table) {
            $table->date('old_attendance_date')
                ->nullable()
                ->after('attendance_date');

            $table->date('new_attendance_date')
                ->nullable()
                ->after('old_attendance_date');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_corrections', function (Blueprint $table) {
            $table->dropColumn([
                'old_attendance_date',
                'new_attendance_date',
            ]);
        });
    }
};