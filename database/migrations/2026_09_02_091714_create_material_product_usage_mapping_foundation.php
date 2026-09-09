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
        /*
        |--------------------------------------------------------------------------
        | Material Product Usage Mappings
        |--------------------------------------------------------------------------
        |
        | Canonical Product Usage intelligence.
        |
        | Product Usage Mapping describes where/how a Product may normally
        | be used. It is NOT mandatory transaction classification for
        | Material Received.
        |
        */

        if (! Schema::hasTable('material_product_usage_mappings')) {
            Schema::create('material_product_usage_mappings', function (Blueprint $table) {
                $table->id();

                /*
                |--------------------------------------------------------------------------
                | Product
                |--------------------------------------------------------------------------
                */

                $table->unsignedBigInteger('material_type_id');

                $table->unsignedBigInteger('material_variant_id')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Usage Classification
                |--------------------------------------------------------------------------
                */

                $table->unsignedBigInteger('activity_division_id')
                    ->nullable();

                $table->unsignedBigInteger('activity_id')
                    ->nullable();

                $table->unsignedBigInteger('construction_work_package_id')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Usage Metadata
                |--------------------------------------------------------------------------
                */

                $table->string('usage_type', 50)
                    ->default('Primary');

                $table->boolean('is_primary')
                    ->default(false);

                $table->unsignedInteger('sort_order')
                    ->default(0);

                $table->boolean('is_active')
                    ->default(true);

                /*
                |--------------------------------------------------------------------------
                | Import / Source Traceability
                |--------------------------------------------------------------------------
                */

                $table->string('source_code', 150)
                    ->nullable();

                $table->string('source_name', 255)
                    ->nullable();

                $table->string('source', 100)
                    ->nullable();

                $table->text('remarks')
                    ->nullable();

                $table->timestamps();

                /*
                |--------------------------------------------------------------------------
                | Foreign Keys
                |--------------------------------------------------------------------------
                |
                | Explicit short names are intentional because MySQL limits
                | identifiers to 64 characters.
                |
                */

                $table->foreign(
                    'material_type_id',
                    'mpum_material_type_fk'
                )
                    ->references('id')
                    ->on('material_types')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreign(
                    'material_variant_id',
                    'mpum_variant_fk'
                )
                    ->references('id')
                    ->on('material_variants')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreign(
                    'activity_division_id',
                    'mpum_division_fk'
                )
                    ->references('id')
                    ->on('activity_divisions')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();

                $table->foreign(
                    'activity_id',
                    'mpum_activity_fk'
                )
                    ->references('id')
                    ->on('activities')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();

                $table->foreign(
                    'construction_work_package_id',
                    'mpum_work_package_fk'
                )
                    ->references('id')
                    ->on('construction_work_packages')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Indexes
                |--------------------------------------------------------------------------
                */

                $table->index(
                    [
                        'material_type_id',
                        'is_active',
                        'is_primary',
                        'sort_order',
                    ],
                    'mpum_product_filter_idx'
                );

                $table->index(
                    [
                        'material_variant_id',
                        'is_active',
                    ],
                    'mpum_variant_filter_idx'
                );

                $table->index(
                    [
                        'activity_division_id',
                        'is_active',
                    ],
                    'mpum_division_filter_idx'
                );

                $table->index(
                    [
                        'activity_id',
                        'is_active',
                    ],
                    'mpum_activity_filter_idx'
                );

                $table->index(
                    [
                        'construction_work_package_id',
                        'is_active',
                    ],
                    'mpum_work_package_filter_idx'
                );

                $table->index(
                    [
                        'usage_type',
                        'is_active',
                    ],
                    'mpum_usage_type_idx'
                );

                $table->index(
                    'source_code',
                    'mpum_source_code_idx'
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_product_usage_mappings');
    }
};