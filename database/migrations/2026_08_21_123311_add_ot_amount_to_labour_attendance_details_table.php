<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'labour_attendance_details',
            function (Blueprint $table): void {
                $table->decimal(
                    'ot_amount',
                    12,
                    2
                )
                    ->nullable()
                    ->after('ot_hours')
                    ->comment(
                        'Manual flat OT amount override. NULL means OT is calculated automatically from hours and OT rate.'
                    );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'labour_attendance_details',
            function (Blueprint $table): void {
                $table->dropColumn(
                    'ot_amount'
                );
            }
        );
    }
};