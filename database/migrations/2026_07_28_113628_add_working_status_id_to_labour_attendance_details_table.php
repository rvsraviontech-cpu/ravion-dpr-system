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
        Schema::table(
            'labour_attendance_details',
            function (Blueprint $table): void {
                $table->foreignId('working_status_id')
                    ->nullable()
                    ->after('attendance_status_id')
                    ->constrained('working_statuses')
                    ->nullOnDelete();

                $table->index(
                    [
                        'attendance_status_id',
                        'working_status_id',
                    ],
                    'lad_attendance_working_status_idx'
                );
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(
            'labour_attendance_details',
            function (Blueprint $table): void {
                $table->dropIndex(
                    'lad_attendance_working_status_idx'
                );

                $table->dropConstrainedForeignId(
                    'working_status_id'
                );
            }
        );
    }
};