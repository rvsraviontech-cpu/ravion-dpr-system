<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'material_catalog_subcategory_material_type',
            function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger(
                    'material_catalog_subcategory_id'
                );

                $table->unsignedBigInteger(
                    'material_type_id'
                );

                $table->boolean('is_preferred')
                    ->default(false);

                $table->unsignedInteger('sort_order')
                    ->default(0);

                $table->boolean('is_active')
                    ->default(true);

                $table->timestamps();

                $table->foreign(
                    'material_catalog_subcategory_id',
                    'mcsm_subcategory_fk'
                )
                    ->references('id')
                    ->on('material_catalog_subcategories')
                    ->cascadeOnDelete();

                $table->foreign(
                    'material_type_id',
                    'mcsm_material_type_fk'
                )
                    ->references('id')
                    ->on('material_types')
                    ->cascadeOnDelete();

                $table->unique(
                    [
                        'material_catalog_subcategory_id',
                        'material_type_id',
                    ],
                    'mcsm_subcategory_material_uq'
                );

                $table->index(
                    [
                        'material_type_id',
                        'is_active',
                    ],
                    'mcsm_material_active_idx'
                );

                $table->index(
                    [
                        'material_catalog_subcategory_id',
                        'is_active',
                        'sort_order',
                    ],
                    'mcsm_subcategory_active_sort_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'material_catalog_subcategory_material_type'
        );
    }
};
