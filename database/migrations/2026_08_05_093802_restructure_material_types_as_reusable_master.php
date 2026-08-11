<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * First remove the foreign-key constraints only.
         * The columns must remain temporarily because existing indexes
         * still depend on them.
         */
        Schema::table('material_types', function (Blueprint $table) {
            $table->dropForeign([
                'activity_division_id',
            ]);

            $table->dropForeign([
                'activity_id',
            ]);
        });

        /*
         * Now remove indexes that use the old activity columns.
         */
        Schema::table('material_types', function (Blueprint $table) {
            $table->dropUnique(
                'material_type_unique'
            );

            $table->dropIndex(
                'material_types_activity_division_id_activity_id_is_active_index'
            );
        });

        /*
         * Remove the old activity-based columns.
         */
        Schema::table('material_types', function (Blueprint $table) {
            $table->dropColumn([
                'activity_division_id',
                'activity_id',
            ]);
        });

        /*
         * Convert Material Types into a reusable central master.
         */
        Schema::table('material_types', function (Blueprint $table) {
            $table->string('material_group')
                ->nullable()
                ->after('id');

            $table->unique(
                'material_type_name',
                'material_types_name_unique'
            );

            $table->index(
                [
                    'material_group',
                    'is_active',
                ],
                'material_types_group_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('material_types', function (Blueprint $table) {
            $table->dropIndex(
                'material_types_group_status_index'
            );

            $table->dropUnique(
                'material_types_name_unique'
            );

            $table->dropColumn('material_group');
        });

        Schema::table('material_types', function (Blueprint $table) {
            $table->foreignId('activity_division_id')
                ->nullable()
                ->after('id')
                ->constrained('activity_divisions')
                ->nullOnDelete();

            $table->foreignId('activity_id')
                ->nullable()
                ->after('activity_division_id')
                ->constrained('activities')
                ->nullOnDelete();
        });

        Schema::table('material_types', function (Blueprint $table) {
            $table->index(
                [
                    'activity_division_id',
                    'activity_id',
                    'is_active',
                ],
                'material_types_activity_division_id_activity_id_is_active_index'
            );

            $table->unique(
                [
                    'activity_id',
                    'material_type_name',
                ],
                'material_type_unique'
            );
        });
    }
};