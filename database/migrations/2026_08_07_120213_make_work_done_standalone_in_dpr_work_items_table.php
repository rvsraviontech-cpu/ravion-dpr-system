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
        | Add project_id
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
        |
        | Use correlated subqueries instead of MySQL UPDATE ... INNER JOIN.
        | This works with both MySQL and SQLite.
        |
        */

        DB::table('dpr_work_items')
            ->whereNotNull('dpr_id')
            ->orderBy('id')
            ->chunkById(200, function ($workItems) {
                foreach ($workItems as $workItem) {
                    $dpr = DB::table('dprs')
                        ->where('id', $workItem->dpr_id)
                        ->first([
                            'project_id',
                            'user_id',
                            'dpr_date',
                        ]);

                    if (! $dpr) {
                        continue;
                    }

                    DB::table('dpr_work_items')
                        ->where('id', $workItem->id)
                        ->update([
                            'project_id' => $workItem->project_id
                                ?? $dpr->project_id,

                            'user_id' => $workItem->user_id
                                ?? $dpr->user_id,

                            'work_date' => $workItem->work_date
                                ?? $dpr->dpr_date,
                        ]);
                }
            });

        /*
        |--------------------------------------------------------------------------
        | Make dpr_id nullable
        |--------------------------------------------------------------------------
        |
        | Work Done is now allowed to exist independently and can later
        | be linked to a DPR.
        |
        */

        Schema::table('dpr_work_items', function (Blueprint $table) {
            $table->foreignId('dpr_id')
                ->nullable()
                ->change();
        });

        /*
        |--------------------------------------------------------------------------
        | Daily Work / DPR Link Index
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
        /*
        |--------------------------------------------------------------------------
        | Remove daily-link index
        |--------------------------------------------------------------------------
        */

        Schema::table('dpr_work_items', function (Blueprint $table) {
            $table->dropIndex(
                'dpr_work_items_daily_link_index'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Remove project_id
        |--------------------------------------------------------------------------
        |
        | We intentionally do NOT force dpr_id back to NOT NULL because
        | standalone Work Done records may already exist.
        |
        */

        if (Schema::hasColumn('dpr_work_items', 'project_id')) {
            Schema::table('dpr_work_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('project_id');
            });
        }
    }
};