<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Add Project ID
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasColumn('dpr_work_items', 'project_id')) {
            Schema::table('dpr_work_items', function (Blueprint $table) {
                $table->foreignId('project_id')
                    ->nullable()
                    ->after('dpr_id')
                    ->constrained('projects')
                    ->nullOnDelete();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Backfill existing Work Done rows from their DPR
        |--------------------------------------------------------------------------
        */

        DB::statement("
            UPDATE dpr_work_items
            INNER JOIN dprs
                ON dprs.id = dpr_work_items.dpr_id
            SET
                dpr_work_items.project_id = dprs.project_id,
                dpr_work_items.user_id = COALESCE(
                    dpr_work_items.user_id,
                    dprs.user_id
                ),
                dpr_work_items.work_date = COALESCE(
                    dpr_work_items.work_date,
                    dprs.dpr_date
                )
            WHERE dpr_work_items.dpr_id IS NOT NULL
        ");

        /*
        |--------------------------------------------------------------------------
        | Make DPR Optional
        |--------------------------------------------------------------------------
        |
        | Work Done is created first.
        | DPR linkage happens later.
        |
        */

        Schema::table('dpr_work_items', function (Blueprint $table) {
            $table->dropForeign(['dpr_id']);
        });

        DB::statement("
            ALTER TABLE dpr_work_items
            MODIFY dpr_id BIGINT UNSIGNED NULL
        ");

        Schema::table('dpr_work_items', function (Blueprint $table) {
            $table->foreign('dpr_id')
                ->references('id')
                ->on('dprs')
                ->nullOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | Helpful Index
        |--------------------------------------------------------------------------
        */

        Schema::table('dpr_work_items', function (Blueprint $table) {
            $table->index(
                [
                    'project_id',
                    'work_date',
                    'user_id',
                    'dpr_id',
                ],
                'dpr_work_items_daily_link_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('dpr_work_items', function (Blueprint $table) {
            $table->dropIndex(
                'dpr_work_items_daily_link_index'
            );
        });

        /*
         * Do not automatically make dpr_id NOT NULL here because
         * standalone records may exist after this migration.
         */

        if (Schema::hasColumn('dpr_work_items', 'project_id')) {
            Schema::table('dpr_work_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('project_id');
            });
        }
    }
};