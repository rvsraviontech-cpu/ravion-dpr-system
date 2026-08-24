<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('labour_attendances', function (Blueprint $table) {
            $table->string('attendance_type', 30)
                ->default('regular')
                ->after('shift_id');

            $table->string('work_session_name', 150)
                ->nullable()
                ->after('attendance_type');
        });
    }

    public function down(): void
    {
        Schema::table('labour_attendances', function (Blueprint $table) {
            $table->dropColumn([
                'attendance_type',
                'work_session_name',
            ]);
        });
    }
};