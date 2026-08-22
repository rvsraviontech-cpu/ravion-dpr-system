<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'attendance_correction_details',
            function (Blueprint $table): void {
                $table->decimal(
                    'old_ot_amount',
                    12,
                    2
                )
                    ->nullable()
                    ->after('old_ot_hours');

                $table->decimal(
                    'new_ot_amount',
                    12,
                    2
                )
                    ->nullable()
                    ->after('new_ot_hours');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'attendance_correction_details',
            function (Blueprint $table): void {
                $table->dropColumn([
                    'old_ot_amount',
                    'new_ot_amount',
                ]);
            }
        );
    }
};