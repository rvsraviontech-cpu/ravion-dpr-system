<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the exact Material relationship to Brand Master.
     */
    public function up(): void
    {
        Schema::table('brand_masters', function (Blueprint $table) {
            $table->foreignId('activity_id')
                ->nullable()
                ->after('material_category_id')
                ->constrained('activities')
                ->nullOnDelete();

            $table->unsignedInteger('sequence')
                ->default(0)
                ->after('brand_code');

            $table->index(
                ['activity_id', 'is_active'],
                'brand_masters_activity_status_index'
            );
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('brand_masters', function (Blueprint $table) {
            $table->dropIndex(
                'brand_masters_activity_status_index'
            );

            $table->dropConstrainedForeignId('activity_id');

            $table->dropColumn('sequence');
        });
    }
};