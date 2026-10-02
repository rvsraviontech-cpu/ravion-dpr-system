<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spatial_space_component_mappings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('spatial_space_type_id');
            $table->unsignedBigInteger('spatial_space_subtype_id')->nullable();
            $table->unsignedBigInteger('spatial_component_type_id')->nullable();
            $table->unsignedBigInteger('spatial_opening_type_id')->nullable();
            $table->unsignedBigInteger('spatial_measurement_zone_id')->nullable();

            $table->string('context_group', 40);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            /*
             * Explicit short FK names are required because MySQL limits
             * identifiers to 64 characters.
             */
            $table->foreign(
                'spatial_space_type_id',
                'sscm_space_type_fk'
            )->references('id')
                ->on('spatial_space_types')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(
                'spatial_space_subtype_id',
                'sscm_space_subtype_fk'
            )->references('id')
                ->on('spatial_space_subtypes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(
                'spatial_component_type_id',
                'sscm_component_type_fk'
            )->references('id')
                ->on('spatial_component_types')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(
                'spatial_opening_type_id',
                'sscm_opening_type_fk'
            )->references('id')
                ->on('spatial_opening_types')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(
                'spatial_measurement_zone_id',
                'sscm_measure_zone_fk'
            )->references('id')
                ->on('spatial_measurement_zones')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->index(
                [
                    'spatial_space_type_id',
                    'spatial_space_subtype_id',
                    'context_group',
                ],
                'sscm_space_context_idx'
            );

            $table->index(
                [
                    'context_group',
                    'is_active',
                    'sort_order',
                ],
                'sscm_context_active_sort_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spatial_space_component_mappings');
    }
};