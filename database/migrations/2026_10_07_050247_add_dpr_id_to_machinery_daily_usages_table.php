<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('machinery_daily_usages', function (Blueprint $table) {
            $table->foreignId('dpr_id')
                ->nullable()
                ->after('project_id')
                ->constrained('dprs')
                ->nullOnDelete();

            $table->index(
                ['project_id', 'usage_date', 'dpr_id'],
                'machinery_daily_usages_dpr_lookup_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machinery_daily_usages', function (Blueprint $table) {
            $table->dropIndex(
                'machinery_daily_usages_dpr_lookup_idx'
            );

            $table->dropConstrainedForeignId('dpr_id');
        });
    }
};