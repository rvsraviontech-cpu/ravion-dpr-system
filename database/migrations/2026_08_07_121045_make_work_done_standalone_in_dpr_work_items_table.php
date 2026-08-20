<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Historical compatibility migration.
     *
     * The Work Done standalone conversion is performed by:
     *
     * 2026_08_07_120213_make_work_done_standalone_in_dpr_work_items_table
     *
     * This migration was created shortly afterward as a defensive
     * replacement for the same schema conversion and duplicated:
     *
     * - project_id creation
     * - DPR data backfill
     * - dpr_id nullable conversion
     * - daily-link index creation
     *
     * Keeping this migration as a no-op preserves the migration history
     * of existing Ravion installations while preventing the conversion
     * from being executed twice on fresh installations and test databases.
     */
    public function up(): void
    {
        //
    }

    /**
     * No rollback action is required because this migration no longer
     * owns any schema changes.
     */
    public function down(): void
    {
        //
    }
};