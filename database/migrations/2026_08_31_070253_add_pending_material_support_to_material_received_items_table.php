<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_received_items', function (Blueprint $table) {
            $table->unsignedBigInteger('pending_material_classification_id')
                ->nullable()
                ->after('construction_work_package_id');

            $table->foreign(
                'pending_material_classification_id',
                'mri_pending_material_fk'
            )
                ->references('id')
                ->on('pending_material_classifications')
                ->nullOnDelete();

            $table->index(
                'pending_material_classification_id',
                'mri_pending_material_idx'
            );
        });

        Schema::table('material_received_items', function (Blueprint $table) {
            $table->unsignedBigInteger('material_type_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        if (
            DB::table('material_received_items')
                ->whereNull('material_type_id')
                ->exists()
        ) {
            throw new \RuntimeException(
                'Cannot rollback pending material support while Material Received items with no Material Type exist.'
            );
        }

        Schema::table('material_received_items', function (Blueprint $table) {
            $table->dropForeign('mri_pending_material_fk');
            $table->dropIndex('mri_pending_material_idx');
            $table->dropColumn('pending_material_classification_id');
        });

        Schema::table('material_received_items', function (Blueprint $table) {
            $table->unsignedBigInteger('material_type_id')
                ->nullable(false)
                ->change();
        });
    }
};
