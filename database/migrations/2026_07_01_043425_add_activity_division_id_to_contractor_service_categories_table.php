<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contractor_service_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('contractor_service_categories', 'activity_division_id')) {
                $table->foreignId('activity_division_id')
                    ->nullable()
                    ->after('work_stage_id')
                    ->constrained('activity_divisions')
                    ->nullOnDelete();
            }
        });

        try {
            DB::statement(
                'CREATE INDEX csc_div_status_idx ON contractor_service_categories (activity_division_id, is_active)'
            );
        } catch (\Throwable $e) {
            // index may already exist; ignore during recovery
        }
    }

    public function down(): void
    {
        Schema::table('contractor_service_categories', function (Blueprint $table) {
            try {
                $table->dropIndex('csc_div_status_idx');
            } catch (\Throwable $e) {
                //
            }

            if (Schema::hasColumn('contractor_service_categories', 'activity_division_id')) {
                try {
                    $table->dropForeign(['activity_division_id']);
                } catch (\Throwable $e) {
                    //
                }

                $table->dropColumn('activity_division_id');
            }
        });
    }
};