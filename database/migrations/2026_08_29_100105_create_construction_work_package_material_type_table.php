<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('construction_work_package_material_type', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('construction_work_package_id');
            $table->unsignedBigInteger('material_type_id');

            $table->boolean('is_preferred')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Foreign Keys
            |--------------------------------------------------------------------------
            |
            | Explicit short constraint names are used because MySQL limits
            | identifier names to 64 characters.
            |
            */

            $table->foreign(
                'construction_work_package_id',
                'cwpm_work_package_fk'
            )
                ->references('id')
                ->on('construction_work_packages')
                ->cascadeOnDelete();

            $table->foreign(
                'material_type_id',
                'cwpm_material_type_fk'
            )
                ->references('id')
                ->on('material_types')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Constraints & Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique(
                [
                    'construction_work_package_id',
                    'material_type_id',
                ],
                'cwpm_package_material_uq'
            );

            $table->index(
                [
                    'material_type_id',
                    'is_active',
                ],
                'cwpm_material_active_idx'
            );

            $table->index(
                [
                    'construction_work_package_id',
                    'is_active',
                    'is_preferred',
                ],
                'cwpm_package_filter_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('construction_work_package_material_type');
    }
};