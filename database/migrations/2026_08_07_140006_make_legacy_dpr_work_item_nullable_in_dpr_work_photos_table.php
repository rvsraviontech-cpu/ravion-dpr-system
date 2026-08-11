<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('dpr_work_photos')
            && Schema::hasColumn(
                'dpr_work_photos',
                'dpr_work_item_id'
            )
        ) {
            /*
            |--------------------------------------------------------------------------
            | Legacy Work Item Reference Becomes Optional
            |--------------------------------------------------------------------------
            |
            | Legacy Work Done photo:
            |
            | dpr_work_item_id  = populated
            | work_done_item_id = NULL
            |
            | Work Done v2 photo:
            |
            | dpr_work_item_id  = NULL
            | work_done_item_id = populated
            |
            */

            DB::statement("
                ALTER TABLE dpr_work_photos
                MODIFY dpr_work_item_id
                BIGINT UNSIGNED NULL
            ");
        }
    }

    public function down(): void
    {
        /*
         * Do NOT automatically make this NOT NULL again.
         *
         * Work Done v2 photos may exist with:
         *
         * dpr_work_item_id = NULL
         *
         * Reverting to NOT NULL would therefore fail and could
         * invalidate legitimate v2 photo records.
         */
    }
};