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
        | Material Catalogue Import Batches
        |--------------------------------------------------------------------------
        |
        | One record represents one catalogue import package / import attempt.
        |
        | Example:
        | Ravion_Materials_ERP_Import_Package_V1.zip
        |
        | The batch provides auditability and allows future catalogue versions
        | to be imported without losing the history of earlier imports.
        |
        */

        if (! Schema::hasTable('material_catalog_import_batches')) {
            Schema::create(
                'material_catalog_import_batches',
                function (Blueprint $table) {
                    $table->id();

                    $table->string('batch_code', 100)
                        ->unique();

                    $table->string('package_name', 255)
                        ->nullable();

                    $table->string('package_version', 100)
                        ->nullable();

                    $table->string('source_file_name', 255)
                        ->nullable();

                    $table->string('source_checksum', 128)
                        ->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Import Lifecycle
                    |--------------------------------------------------------------------------
                    |
                    | Suggested statuses:
                    |
                    | Draft
                    | Staging
                    | Staged
                    | Validating
                    | Validated
                    | Importing
                    | Imported
                    | Failed
                    | Cancelled
                    |
                    */

                    $table->string('status', 50)
                        ->default('Draft');

                    /*
                    |--------------------------------------------------------------------------
                    | Package Counts
                    |--------------------------------------------------------------------------
                    */

                    $table->unsignedInteger('total_product_rows')
                        ->default(0);

                    $table->unsignedInteger('total_specification_rows')
                        ->default(0);

                    $table->unsignedInteger('total_brand_rows')
                        ->default(0);

                    $table->unsignedInteger('total_product_brand_rows')
                        ->default(0);

                    $table->unsignedInteger('total_unit_mapping_rows')
                        ->default(0);

                    $table->unsignedInteger('total_usage_mapping_rows')
                        ->default(0);

                    $table->unsignedInteger('total_search_alias_rows')
                        ->default(0);

                    $table->unsignedInteger('total_audit_rows')
                        ->default(0);

                    /*
                    |--------------------------------------------------------------------------
                    | Staging / Review Counts
                    |--------------------------------------------------------------------------
                    */

                    $table->unsignedInteger('staged_product_rows')
                        ->default(0);

                    $table->unsignedInteger('ready_product_rows')
                        ->default(0);

                    $table->unsignedInteger('review_product_rows')
                        ->default(0);

                    $table->unsignedInteger('excluded_product_rows')
                        ->default(0);

                    $table->unsignedInteger('error_product_rows')
                        ->default(0);

                    $table->unsignedInteger('imported_product_rows')
                        ->default(0);

                    /*
                    |--------------------------------------------------------------------------
                    | Audit
                    |--------------------------------------------------------------------------
                    */

                    $table->unsignedBigInteger('created_by')
                        ->nullable();

                    $table->unsignedBigInteger('started_by')
                        ->nullable();

                    $table->unsignedBigInteger('completed_by')
                        ->nullable();

                    $table->timestamp('started_at')
                        ->nullable();

                    $table->timestamp('completed_at')
                        ->nullable();

                    $table->text('remarks')
                        ->nullable();

                    $table->text('error_message')
                        ->nullable();

                    $table->timestamps();

                    /*
                    |--------------------------------------------------------------------------
                    | Indexes
                    |--------------------------------------------------------------------------
                    */

                    $table->index(
                        ['status', 'created_at'],
                        'mcib_status_created_idx'
                    );

                    $table->index(
                        'source_checksum',
                        'mcib_checksum_idx'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | User Foreign Keys
                    |--------------------------------------------------------------------------
                    |
                    | Short explicit names avoid MySQL's 64-character
                    | identifier limit.
                    |
                    */

                    $table->foreign(
                        'created_by',
                        'mcib_created_by_fk'
                    )
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();

                    $table->foreign(
                        'started_by',
                        'mcib_started_by_fk'
                    )
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();

                    $table->foreign(
                        'completed_by',
                        'mcib_completed_by_fk'
                    )
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Material Catalogue Product Staging
        |--------------------------------------------------------------------------
        |
        | This is NOT the Product Master.
        |
        | Every Product Master CSV row first lands here.
        |
        | Only after validation/review will an approved staging row create or
        | link to a canonical material_types record.
        |
        */

        if (! Schema::hasTable('material_catalog_product_staging')) {
            Schema::create(
                'material_catalog_product_staging',
                function (Blueprint $table) {
                    $table->id();

                    $table->unsignedBigInteger('import_batch_id');

                    /*
                    |--------------------------------------------------------------------------
                    | Source Identity
                    |--------------------------------------------------------------------------
                    */

                    $table->unsignedInteger('source_row_number')
                        ->nullable();

                    $table->string('source_product_code', 150);

                    /*
                    |--------------------------------------------------------------------------
                    | Source Classification
                    |--------------------------------------------------------------------------
                    */

                    $table->string('product_group', 255)
                        ->nullable();

                    $table->string('product_type', 255)
                        ->nullable();

                    $table->string('product_name', 500);

                    $table->string('inventory_type', 150)
                        ->nullable();

                    $table->string('default_unit', 100)
                        ->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Source Master Status
                    |--------------------------------------------------------------------------
                    |
                    | Preserve the package's terminology exactly.
                    |
                    | Examples:
                    | Provisional Ready
                    | Review
                    |
                    */

                    $table->string('source_master_status', 100)
                        ->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Normalized Search / Comparison Values
                    |--------------------------------------------------------------------------
                    |
                    | These fields help validation without modifying the original
                    | catalogue values.
                    |
                    */

                    $table->string('normalized_product_name', 500)
                        ->nullable();

                    $table->string('normalized_product_group', 255)
                        ->nullable();

                    $table->string('normalized_product_type', 255)
                        ->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Review / Import Decision
                    |--------------------------------------------------------------------------
                    |
                    | Suggested values:
                    |
                    | Pending
                    | Ready
                    | Review
                    | Excluded
                    | Error
                    | Imported
                    |
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
                    |
                    | These are populated only after the staging row is approved
                    | and mapped/imported into the canonical Product Master.
                    |
                    */

                    $table->unsignedBigInteger('material_product_group_id')
                        ->nullable();

                    $table->unsignedBigInteger('material_product_type_id')
                        ->nullable();

                    $table->unsignedBigInteger('unit_master_id')
                        ->nullable();

                    $table->unsignedBigInteger('material_type_id')
                        ->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Review / Import Audit
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
                        'mcps_batch_fk'
                    )
                        ->references('id')
                        ->on('material_catalog_import_batches')
                        ->cascadeOnDelete();

                    $table->foreign(
                        'material_product_group_id',
                        'mcps_group_fk'
                    )
                        ->references('id')
                        ->on('material_product_groups')
                        ->nullOnDelete();

                    $table->foreign(
                        'material_product_type_id',
                        'mcps_type_fk'
                    )
                        ->references('id')
                        ->on('material_product_types')
                        ->nullOnDelete();

                    $table->foreign(
                        'unit_master_id',
                        'mcps_unit_fk'
                    )
                        ->references('id')
                        ->on('unit_masters')
                        ->nullOnDelete();

                    $table->foreign(
                        'material_type_id',
                        'mcps_product_fk'
                    )
                        ->references('id')
                        ->on('material_types')
                        ->nullOnDelete();

                    $table->foreign(
                        'reviewed_by',
                        'mcps_reviewed_by_fk'
                    )
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();

                    $table->foreign(
                        'imported_by',
                        'mcps_imported_by_fk'
                    )
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();

                    /*
                    |--------------------------------------------------------------------------
                    | Duplicate Protection
                    |--------------------------------------------------------------------------
                    |
                    | A source Product Code may appear once per import batch.
                    |
                    */

                    $table->unique(
                        [
                            'import_batch_id',
                            'source_product_code',
                        ],
                        'mcps_batch_product_uq'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Search / Validation Indexes
                    |--------------------------------------------------------------------------
                    */

                    $table->index(
                        [
                            'import_batch_id',
                            'staging_status',
                        ],
                        'mcps_batch_status_idx'
                    );

                    $table->index(
                        [
                            'import_batch_id',
                            'source_master_status',
                        ],
                        'mcps_batch_master_status_idx'
                    );

                    $table->index(
                        'normalized_product_name',
                        'mcps_product_name_idx'
                    );

                    $table->index(
                        [
                            'product_group',
                            'product_type',
                        ],
                        'mcps_classification_idx'
                    );

                    $table->index(
                        'material_type_id',
                        'mcps_material_type_idx'
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
            'material_catalog_product_staging'
        );

        Schema::dropIfExists(
            'material_catalog_import_batches'
        );
    }
};