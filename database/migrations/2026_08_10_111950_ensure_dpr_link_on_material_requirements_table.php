<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Most execution tables already contain nullable dpr_id columns.
         * This migration safely completes the link architecture for databases
         * where Material Requirements was created before DPR linking was added.
         */

        if (
            Schema::hasTable('material_requirements')
            && ! Schema::hasColumn(
                'material_requirements',
                'dpr_id'
            )
        ) {
            Schema::table(
                'material_requirements',
                function (Blueprint $table): void {
                    $table->foreignId('dpr_id')
                        ->nullable()
                        ->after('id')
                        ->constrained('dprs')
                        ->nullOnDelete();

                    $table->index(
                        ['project_id', 'dpr_id'],
                        'material_requirements_project_dpr_index'
                    );
                }
            );
        }
    }

    public function down(): void
    {
        /*
         * Intentionally conservative.
         *
         * Do not remove dpr_id automatically because the column may have been
         * introduced by an earlier migration and may already contain DPR links.
         */
    }
};
