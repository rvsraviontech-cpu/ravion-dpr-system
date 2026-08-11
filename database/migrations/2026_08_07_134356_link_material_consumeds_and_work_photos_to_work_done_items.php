<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('material_consumeds', 'work_done_item_id')) {
            Schema::table('material_consumeds', function (Blueprint $table) {
                $table->foreignId('work_done_item_id')
                    ->nullable()
                    ->after('dpr_id')
                    ->constrained('work_done_items')
                    ->nullOnDelete();

                $table->index(
                    ['work_done_item_id', 'consumed_date'],
                    'material_consumeds_work_item_date_index'
                );
            });
        }

        if (
            Schema::hasTable('dpr_work_photos')
            && ! Schema::hasColumn('dpr_work_photos', 'work_done_item_id')
        ) {
            Schema::table('dpr_work_photos', function (Blueprint $table) {
                $table->foreignId('work_done_item_id')
                    ->nullable()
                    ->after('dpr_work_item_id')
                    ->constrained('work_done_items')
                    ->cascadeOnDelete();

                $table->index(
                    ['work_done_item_id', 'sort_order'],
                    'dpr_work_photos_work_item_sort_index'
                );
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('dpr_work_photos')
            && Schema::hasColumn('dpr_work_photos', 'work_done_item_id')
        ) {
            Schema::table('dpr_work_photos', function (Blueprint $table) {
                $table->dropIndex(
                    'dpr_work_photos_work_item_sort_index'
                );

                $table->dropConstrainedForeignId(
                    'work_done_item_id'
                );
            });
        }

        if (Schema::hasColumn('material_consumeds', 'work_done_item_id')) {
            Schema::table('material_consumeds', function (Blueprint $table) {
                $table->dropIndex(
                    'material_consumeds_work_item_date_index'
                );

                $table->dropConstrainedForeignId(
                    'work_done_item_id'
                );
            });
        }
    }
};
