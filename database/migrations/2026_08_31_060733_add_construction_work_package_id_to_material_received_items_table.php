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
        Schema::table('material_received_items', function (Blueprint $table) {
            $table->unsignedBigInteger('construction_work_package_id')
                ->nullable()
                ->after('activity_id');

            $table->foreign(
                'construction_work_package_id',
                'mri_work_package_fk'
            )
                ->references('id')
                ->on('construction_work_packages')
                ->nullOnDelete();

            $table->index(
                [
                    'construction_work_package_id',
                    'material_type_id',
                ],
                'mri_work_package_material_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_received_items', function (Blueprint $table) {
            $table->dropForeign('mri_work_package_fk');
            $table->dropIndex('mri_work_package_material_idx');

            $table->dropColumn('construction_work_package_id');
        });
    }
};