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
        | Add project_id if not already present
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
        | Backfill existing DPR-linked Work Done records
        |--------------------------------------------------------------------------
        */

        DB::statement("
            UPDATE dpr_work_items
            INNER JOIN dprs
                ON dprs.id = dpr_work_items.dpr_id
            SET
                dpr_work_items.project_id = COALESCE(
                    dpr_work_items.project_id,
                    dprs.project_id
                ),
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
        | Make dpr_id nullable
        |--------------------------------------------------------------------------
        |
        | Work Done is created independently.
        | It gets linked to a DPR later.
        |--------------------------------------------------------------------------
        */

        $dprForeignKey = $this->foreignKeyName(
            'dpr_work_items',
            'dpr_id',
            'dprs'
        );

        if ($dprForeignKey !== null) {
            DB::statement(
                'ALTER TABLE `dpr_work_items` DROP FOREIGN KEY `'
                . $dprForeignKey
                . '`'
            );
        }

        DB::statement("
            ALTER TABLE dpr_work_items
            MODIFY dpr_id BIGINT UNSIGNED NULL
        ");

        /*
        |--------------------------------------------------------------------------
        | Restore DPR foreign key only if missing
        |--------------------------------------------------------------------------
        */

        if (
            $this->foreignKeyName(
                'dpr_work_items',
                'dpr_id',
                'dprs'
            ) === null
        ) {
            Schema::table('dpr_work_items', function (Blueprint $table) {
                $table->foreign('dpr_id')
                    ->references('id')
                    ->on('dprs')
                    ->nullOnDelete();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Daily Work / DPR Link Index
        |--------------------------------------------------------------------------
        */

        if (
            ! $this->indexExists(
                'dpr_work_items',
                'dpr_work_items_daily_link_index'
            )
        ) {
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
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Remove daily-link index
        |--------------------------------------------------------------------------
        */

        if (
            $this->indexExists(
                'dpr_work_items',
                'dpr_work_items_daily_link_index'
            )
        ) {
            Schema::table('dpr_work_items', function (Blueprint $table) {
                $table->dropIndex(
                    'dpr_work_items_daily_link_index'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Remove project_id
        |--------------------------------------------------------------------------
        |
        | We intentionally do NOT force dpr_id back to NOT NULL.
        | Standalone Work Done records may already exist.
        |--------------------------------------------------------------------------
        */

        if (Schema::hasColumn('dpr_work_items', 'project_id')) {
            Schema::table('dpr_work_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('project_id');
            });
        }
    }

    /**
     * Check whether an index already exists.
     */
    private function indexExists(
        string $table,
        string $indexName
    ): bool {
        $result = DB::selectOne(
            "
                SELECT COUNT(*) AS aggregate
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND INDEX_NAME = ?
            ",
            [
                $table,
                $indexName,
            ]
        );

        return (int) ($result->aggregate ?? 0) > 0;
    }

    /**
     * Return the actual MySQL foreign-key constraint name.
     */
    private function foreignKeyName(
        string $table,
        string $column,
        string $referencedTable
    ): ?string {
        $result = DB::selectOne(
            "
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND COLUMN_NAME = ?
                  AND REFERENCED_TABLE_NAME = ?
                LIMIT 1
            ",
            [
                $table,
                $column,
                $referencedTable,
            ]
        );

        return $result?->CONSTRAINT_NAME ?? null;
    }
};