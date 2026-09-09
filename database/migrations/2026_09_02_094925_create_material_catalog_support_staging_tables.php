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
        | Product Specification Staging
        |--------------------------------------------------------------------------
        |
        | Source:
        | 02_Product_Specification_Master.csv
        |
        | Specifications are staged separately because they may resolve to:
        | - existing specification
        | - new specification
        | - variant-level data
        | - review / exclusion
        |
        */

        if (! Schema::hasTable('material_catalog_specification_staging')) {
            Schema::create(
                'material_catalog_specification_staging',
                function (Blueprint $table) {
                    $table->id();

                    $table->unsignedBigInteger('import_batch_id');

                    $table->unsignedInteger('source_row_number')
                        ->nullable();

                    $table->string('source_product_code', 150);

                    $table->string('source_specification_code', 150)
                        ->nullable();

                    $table->string('specification_name', 500)
                        ->nullable();

                    $table->string('size_dimension', 255)
                        ->nullable();

                    $table->string('grade_class', 255)
                        ->nullable();

                    $table->string('finish', 255)
                        ->nullable();

                    $table->string('colour_shade', 255)
                        ->nullable();

                    $table->string('source_status', 100)
                        ->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Normalized Values
                    |--------------------------------------------------------------------------
                    */

                    $table->string('normalized_specification_name', 500)
                        ->nullable();

                    $table->string('normalized_size_dimension', 255)
                        ->nullable();

                    $table->string('normalized_grade_class', 255)
                        ->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Staging Decision
                    |--------------------------------------------------------------------------
                    */

                    $table->string('staging_status', 50)
                        ->default('Pending');

                    $table->string('review_reason', 500)
                        ->nullable();

                    $table->string('exclusion_reason', 500)
                        ->nullable();

                    $table->text('validation_errors')
                        ->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Canonical Resolution
                    |--------------------------------------------------------------------------
                    */

                    $table->unsignedBigInteger('material_type_id')
                        ->nullable();

                    $table->unsignedBigInteger('material_specification_id')
                        ->nullable();

                    $table->unsignedBigInteger('material_grade_id')
                        ->nullable();

                    $table->unsignedBigInteger('material_variant_id')
                        ->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Audit
                    |--------------------------------------------------------------------------
                    */

                    $table->unsignedBigInteger('reviewed_by')
                        ->nullable();

                    $table->timestamp('reviewed_at')
                        ->nullable();

                    $table->unsignedBigInteger('imported_by')
                        ->nullable();

                    $table->timestamp('imported_at')
                        ->nullable();

                    $table->text('remarks')
                        ->nullable();

                    $table->timestamps();

                    /*
                    |--------------------------------------------------------------------------
                    | Foreign Keys
                    |--------------------------------------------------------------------------
                    */

                    $table->foreign(
                        'import_batch_id',
                        'mcss_batch_fk'
                    )
                        ->references('id')
                        ->on('material_catalog_import_batches')
                        ->cascadeOnDelete();

                    $table->foreign(
                        'material_type_id',
                        'mcss_product_fk'
                    )
                        ->references('id')
                        ->on('material_types')
                        ->nullOnDelete();

                    $table->foreign(
                        'material_specification_id',
                        'mcss_spec_fk'
                    )
                        ->references('id')
                        ->on('material_specifications')
                        ->nullOnDelete();

                    $table->foreign(
                        'material_grade_id',
                        'mcss_grade_fk'
                    )
                        ->references('id')
                        ->on('material_grades')
                        ->nullOnDelete();

                    $table->foreign(
                        'material_variant_id',
                        'mcss_variant_fk'
                    )
                        ->references('id')
                        ->on('material_variants')
                        ->nullOnDelete();

                    $table->foreign(
                        'reviewed_by',
                        'mcss_reviewed_by_fk'
                    )
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();

                    $table->foreign(
                        'imported_by',
                        'mcss_imported_by_fk'
                    )
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();

                    /*
                    |--------------------------------------------------------------------------
                    | Indexes
                    |--------------------------------------------------------------------------
                    */

                    $table->index(
                        [
                            'import_batch_id',
                            'source_product_code',
                        ],
                        'mcss_batch_product_idx'
                    );

                    $table->index(
                        [
                            'import_batch_id',
                            'staging_status',
                        ],
                        'mcss_batch_status_idx'
                    );

                    $table->index(
                        'normalized_specification_name',
                        'mcss_spec_name_idx'
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Unit Mapping Staging
        |--------------------------------------------------------------------------
        |
        | Source:
        | 05_Unit_Master_Mapping.csv
        |
        | This lets us reconcile the package's unit vocabulary against the
        | existing unit_masters table without creating duplicate units.
        |
        */

        if (! Schema::hasTable('material_catalog_unit_mapping_staging')) {
            Schema::create(
                'material_catalog_unit_mapping_staging',
                function (Blueprint $table) {
                    $table->id();

                    $table->unsignedBigInteger('import_batch_id');

                    $table->unsignedInteger('source_row_number')
                        ->nullable();

                    $table->string('source_unit_code', 100)
                        ->nullable();

                    $table->string('source_unit_name', 255);

                    $table->string('source_unit_symbol', 100)
                        ->nullable();

                    $table->string('normalized_unit_name', 255)
                        ->nullable();

                    $table->string('staging_status', 50)
                        ->default('Pending');

                    $table->string('review_reason', 500)
                        ->nullable();

                    $table->text('validation_errors')
                        ->nullable();

                    $table->unsignedBigInteger('unit_master_id')
                        ->nullable();

                    $table->unsignedBigInteger('reviewed_by')
                        ->nullable();

                    $table->timestamp('reviewed_at')
                        ->nullable();

                    $table->text('remarks')
                        ->nullable();

                    $table->timestamps();

                    $table->foreign(
                        'import_batch_id',
                        'mcums_batch_fk'
                    )
                        ->references('id')
                        ->on('material_catalog_import_batches')
                        ->cascadeOnDelete();

                    $table->foreign(
                        'unit_master_id',
                        'mcums_unit_fk'
                    )
                        ->references('id')
                        ->on('unit_masters')
                        ->nullOnDelete();

                    $table->foreign(
                        'reviewed_by',
                        'mcums_reviewed_by_fk'
                    )
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();

                    $table->index(
                        [
                            'import_batch_id',
                            'staging_status',
                        ],
                        'mcums_batch_status_idx'
                    );

                    $table->index(
                        'normalized_unit_name',
                        'mcums_unit_name_idx'
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Product Usage Mapping Staging
        |--------------------------------------------------------------------------
        |
        | Source:
        | 06_Product_Usage_Mapping.csv
        |
        | This table preserves the source classification exactly.
        |
        | It does NOT expose these mappings to Material Received entry.
        |
        */

        if (! Schema::hasTable('material_catalog_usage_mapping_staging')) {
            Schema::create(
                'material_catalog_usage_mapping_staging',
                function (Blueprint $table) {
                    $table->id();

                    $table->unsignedBigInteger('import_batch_id');

                    $table->unsignedInteger('source_row_number')
                        ->nullable();

                    $table->string('source_product_code', 150);

                    $table->string('division_code', 100)
                        ->nullable();

                    $table->string('division_name', 255)
                        ->nullable();

                    $table->string('usage_code', 150)
                        ->nullable();

                    $table->string('usage_name', 500)
                        ->nullable();

                    $table->string('usage_type', 100)
                        ->nullable();

                    $table->boolean('is_primary')
                        ->default(false);

                    $table->string('source_status', 100)
                        ->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Staging Decision
                    |--------------------------------------------------------------------------
                    */

                    $table->string('staging_status', 50)
                        ->default('Pending');

                    $table->string('review_reason', 500)
                        ->nullable();

                    $table->string('exclusion_reason', 500)
                        ->nullable();

                    $table->text('validation_errors')
                        ->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Canonical Resolution
                    |--------------------------------------------------------------------------
                    */

                    $table->unsignedBigInteger('material_type_id')
                        ->nullable();

                    $table->unsignedBigInteger('activity_division_id')
                        ->nullable();

                    $table->unsignedBigInteger('activity_id')
                        ->nullable();

                    $table->unsignedBigInteger('construction_work_package_id')
                        ->nullable();

                    $table->unsignedBigInteger('material_product_usage_mapping_id')
                        ->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Audit
                    |--------------------------------------------------------------------------
                    */

                    $table->unsignedBigInteger('reviewed_by')
                        ->nullable();

                    $table->timestamp('reviewed_at')
                        ->nullable();

                    $table->unsignedBigInteger('imported_by')
                        ->nullable();

                    $table->timestamp('imported_at')
                        ->nullable();

                    $table->text('remarks')
                        ->nullable();

                    $table->timestamps();

                    /*
                    |--------------------------------------------------------------------------
                    | Foreign Keys
                    |--------------------------------------------------------------------------
                    */

                    $table->foreign(
                        'import_batch_id',
                        'mcums2_batch_fk'
                    )
                        ->references('id')
                        ->on('material_catalog_import_batches')
                        ->cascadeOnDelete();

                    $table->foreign(
                        'material_type_id',
                        'mcums2_product_fk'
                    )
                        ->references('id')
                        ->on('material_types')
                        ->nullOnDelete();

                    $table->foreign(
                        'activity_division_id',
                        'mcums2_division_fk'
                    )
                        ->references('id')
                        ->on('activity_divisions')
                        ->nullOnDelete();

                    $table->foreign(
                        'activity_id',
                        'mcums2_activity_fk'
                    )
                        ->references('id')
                        ->on('activities')
                        ->nullOnDelete();

                    $table->foreign(
                        'construction_work_package_id',
                        'mcums2_work_package_fk'
                    )
                        ->references('id')
                        ->on('construction_work_packages')
                        ->nullOnDelete();

                    $table->foreign(
                        'material_product_usage_mapping_id',
                        'mcums2_usage_fk'
                    )
                        ->references('id')
                        ->on('material_product_usage_mappings')
                        ->nullOnDelete();

                    $table->foreign(
                        'reviewed_by',
                        'mcums2_reviewed_by_fk'
                    )
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();

                    $table->foreign(
                        'imported_by',
                        'mcums2_imported_by_fk'
                    )
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();

                    /*
                    |--------------------------------------------------------------------------
                    | Indexes
                    |--------------------------------------------------------------------------
                    */

                    $table->index(
                        [
                            'import_batch_id',
                            'source_product_code',
                        ],
                        'mcums2_batch_product_idx'
                    );

                    $table->index(
                        [
                            'division_code',
                            'usage_code',
                        ],
                        'mcums2_usage_lookup_idx'
                    );

                    $table->index(
                        [
                            'import_batch_id',
                            'staging_status',
                        ],
                        'mcums2_batch_status_idx'
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Search Alias Staging
        |--------------------------------------------------------------------------
        |
        | Source:
        | 07_Search_Alias_Master.csv
        |
        | Alias rows remain separate from the canonical alias table until the
        | Product itself has been resolved/imported.
        |
        */

        if (! Schema::hasTable('material_catalog_search_alias_staging')) {
            Schema::create(
                'material_catalog_search_alias_staging',
                function (Blueprint $table) {
                    $table->id();

                    $table->unsignedBigInteger('import_batch_id');

                    $table->unsignedInteger('source_row_number')
                        ->nullable();

                    $table->string('source_product_code', 150);

                    $table->string('alias', 500);

                    $table->string('normalized_alias', 500)
                        ->nullable();

                    $table->string('alias_type', 100)
                        ->nullable();

                    $table->string('source', 100)
                        ->nullable();

                    $table->string('staging_status', 50)
                        ->default('Pending');

                    $table->string('review_reason', 500)
                        ->nullable();

                    $table->text('validation_errors')
                        ->nullable();

                    $table->unsignedBigInteger('material_type_id')
                        ->nullable();

                    $table->unsignedBigInteger('material_variant_id')
                        ->nullable();

                    $table->unsignedBigInteger('material_search_alias_id')
                        ->nullable();

                    $table->unsignedBigInteger('reviewed_by')
                        ->nullable();

                    $table->timestamp('reviewed_at')
                        ->nullable();

                    $table->unsignedBigInteger('imported_by')
                        ->nullable();

                    $table->timestamp('imported_at')
                        ->nullable();

                    $table->text('remarks')
                        ->nullable();

                    $table->timestamps();

                    $table->foreign(
                        'import_batch_id',
                        'mcsas_batch_fk'
                    )
                        ->references('id')
                        ->on('material_catalog_import_batches')
                        ->cascadeOnDelete();

                    $table->foreign(
                        'material_type_id',
                        'mcsas_product_fk'
                    )
                        ->references('id')
                        ->on('material_types')
                        ->nullOnDelete();

                    $table->foreign(
                        'material_variant_id',
                        'mcsas_variant_fk'
                    )
                        ->references('id')
                        ->on('material_variants')
                        ->nullOnDelete();

                    $table->foreign(
                        'material_search_alias_id',
                        'mcsas_alias_fk'
                    )
                        ->references('id')
                        ->on('material_search_aliases')
                        ->nullOnDelete();

                    $table->foreign(
                        'reviewed_by',
                        'mcsas_reviewed_by_fk'
                    )
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();

                    $table->foreign(
                        'imported_by',
                        'mcsas_imported_by_fk'
                    )
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();

                    $table->index(
                        [
                            'import_batch_id',
                            'source_product_code',
                        ],
                        'mcsas_batch_product_idx'
                    );

                    $table->index(
                        'normalized_alias',
                        'mcsas_alias_search_idx'
                    );

                    $table->index(
                        [
                            'import_batch_id',
                            'staging_status',
                        ],
                        'mcsas_batch_status_idx'
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Import Audit Staging
        |--------------------------------------------------------------------------
        |
        | Source:
        | 08_Import_Audit.csv
        |
        | This preserves the catalogue package's own audit / quality-control
        | information independently of Ravion ERP's eventual import decision.
        |
        */

        if (! Schema::hasTable('material_catalog_import_audit_staging')) {
            Schema::create(
                'material_catalog_import_audit_staging',
                function (Blueprint $table) {
                    $table->id();

                    $table->unsignedBigInteger('import_batch_id');

                    $table->unsignedInteger('source_row_number')
                        ->nullable();

                    $table->string('source_product_code', 150)
                        ->nullable();

                    $table->string('audit_type', 150)
                        ->nullable();

                    $table->string('audit_status', 100)
                        ->nullable();

                    $table->string('severity', 50)
                        ->nullable();

                    $table->string('field_name', 255)
                        ->nullable();

                    $table->text('source_value')
                        ->nullable();

                    $table->text('message')
                        ->nullable();

                    $table->text('recommended_action')
                        ->nullable();

                    $table->string('staging_status', 50)
                        ->default('Pending');

                    $table->unsignedBigInteger('material_type_id')
                        ->nullable();

                    $table->unsignedBigInteger('reviewed_by')
                        ->nullable();

                    $table->timestamp('reviewed_at')
                        ->nullable();

                    $table->text('remarks')
                        ->nullable();

                    $table->timestamps();

                    $table->foreign(
                        'import_batch_id',
                        'mcias_batch_fk'
                    )
                        ->references('id')
                        ->on('material_catalog_import_batches')
                        ->cascadeOnDelete();

                    $table->foreign(
                        'material_type_id',
                        'mcias_product_fk'
                    )
                        ->references('id')
                        ->on('material_types')
                        ->nullOnDelete();

                    $table->foreign(
                        'reviewed_by',
                        'mcias_reviewed_by_fk'
                    )
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();

                    $table->index(
                        [
                            'import_batch_id',
                            'source_product_code',
                        ],
                        'mcias_batch_product_idx'
                    );

                    $table->index(
                        [
                            'audit_status',
                            'severity',
                        ],
                        'mcias_audit_status_idx'
                    );

                    $table->index(
                        [
                            'import_batch_id',
                            'staging_status',
                        ],
                        'mcias_batch_status_idx'
                    );
                }
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'material_catalog_import_audit_staging'
        );

        Schema::dropIfExists(
            'material_catalog_search_alias_staging'
        );

        Schema::dropIfExists(
            'material_catalog_usage_mapping_staging'
        );

        Schema::dropIfExists(
            'material_catalog_unit_mapping_staging'
        );

        Schema::dropIfExists(
            'material_catalog_specification_staging'
        );
    }
};