<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('work_done_item_labours', 'labour_group_id')) {
            Schema::table('work_done_item_labours', function (Blueprint $table): void {
                $table->foreignId('labour_group_id')
                    ->nullable()
                    ->after('designation_role_id')
                    ->constrained('labour_groups')
                    ->restrictOnDelete();
            });
        }

        if (Schema::hasColumn('work_done_item_labours', 'designation_role_id')) {
            Schema::table('work_done_item_labours', function (Blueprint $table): void {
                $table->unsignedBigInteger('designation_role_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Existing group-based rows cannot satisfy the legacy NOT NULL constraint.
        // Keep this migration non-destructive to preserve recorded labour history.
    }
};
