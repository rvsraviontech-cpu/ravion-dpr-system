<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        | Material Product Groups
        |--------------------------------------------------------------------------
        |
        | Canonical classification layer:
        |
        | Product Group
        |     ↓
        | Product Type
        |     ↓
        | Material Type / Product
        |
        | This is intentionally independent from Activity Division, Activity
        | and Construction Work Package.
        |
        */

        if (! Schema::hasTable('material_product_groups')) {
            Schema::create('material_product_groups', function (Blueprint $table) {
                $table->id();

                $table->string('group_code', 100)->nullable();
                $table->string('group_name', 255);

                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);

                $table->text('remarks')->nullable();

                $table->timestamps();

                $table->unique(
                    'group_name',
                    'material_product_groups_name_uq'
                );

                $table->unique(
                    'group_code',
                    'material_product_groups_code_uq'
                );

                $table->index(
                    ['is_active', 'sort_order'],
                    'material_product_groups_active_sort_idx'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Material Product Types
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasTable('material_product_types')) {
            Schema::create('material_product_types', function (Blueprint $table) {
                $table->id();

                $table->foreignId('material_product_group_id')
                    ->constrained('material_product_groups')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->string('type_code', 100)->nullable();
                $table->string('type_name', 255);

                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);

                $table->text('remarks')->nullable();

                $table->timestamps();

                $table->unique(
                    ['material_product_group_id', 'type_name'],
                    'material_product_types_group_name_uq'
                );

                $table->unique(
                    ['material_product_group_id', 'type_code'],
                    'material_product_types_group_code_uq'
                );

                $table->index(
                    ['material_product_group_id', 'is_active', 'sort_order'],
                    'material_product_types_filter_idx'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Extend Existing Material Types
        |--------------------------------------------------------------------------
        |
        | material_types remains the canonical Product Master table.
        |
        | Existing 177 records are preserved.
        | No current foreign keys are changed.
        |
        */

        Schema::table('material_types', function (Blueprint $table) {
            if (! Schema::hasColumn('material_types', 'material_product_group_id')) {
                $table->foreignId('material_product_group_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('material_product_groups')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('material_types', 'material_product_type_id')) {
                $table->foreignId('material_product_type_id')
                    ->nullable()
                    ->after('material_product_group_id')
                    ->constrained('material_product_types')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('material_types', 'catalogue_source_code')) {
                $table->string('catalogue_source_code', 150)
                    ->nullable()
                    ->after('material_type_code');
            }

            if (! Schema::hasColumn('material_types', 'inventory_type')) {
                $table->string('inventory_type', 100)
                    ->nullable()
                    ->after('catalogue_source_code');
            }

            if (! Schema::hasColumn('material_types', 'master_status')) {
                $table->string('master_status', 50)
                    ->default('Approved')
                    ->after('inventory_type');
            }

            if (! Schema::hasColumn('material_types', 'is_legacy')) {
                $table->boolean('is_legacy')
                    ->default(false)
                    ->after('master_status');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Supporting Indexes
        |--------------------------------------------------------------------------
        */

        $this->addIndexIfMissing(
            'material_types',
            'material_types_product_classification_idx',
            [
                'material_product_group_id',
                'material_product_type_id',
                'is_active',
            ]
        );

        $this->addIndexIfMissing(
            'material_types',
            'material_types_catalogue_source_idx',
            [
                'catalogue_source_code',
            ]
        );

        $this->addIndexIfMissing(
            'material_types',
            'material_types_master_status_idx',
            [
                'master_status',
                'is_active',
            ]
        );

        $this->addIndexIfMissing(
            'material_types',
            'material_types_legacy_idx',
            [
                'is_legacy',
                'is_active',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Existing Material Types = Legacy Ravion Master
        |--------------------------------------------------------------------------
        |
        | At the moment this migration is introduced, all currently existing
        | material_types belong to the legacy Ravion Materials Master.
        |
        | Future catalogue imports will explicitly create records with
        | is_legacy = false.
        |
        */

        DB::table('material_types')
            ->whereNull('material_product_group_id')
            ->whereNull('material_product_type_id')
            ->update([
                'is_legacy' => true,
                'master_status' => 'Approved',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Remove Product Master Classification Fields
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('material_types')) {
            Schema::table('material_types', function (Blueprint $table) {
                if (Schema::hasColumn('material_types', 'material_product_type_id')) {
                    $table->dropForeign(['material_product_type_id']);
                }

                if (Schema::hasColumn('material_types', 'material_product_group_id')) {
                    $table->dropForeign(['material_product_group_id']);
                }
            });

            Schema::table('material_types', function (Blueprint $table) {
                $columns = [
                    'material_product_group_id',
                    'material_product_type_id',
                    'catalogue_source_code',
                    'inventory_type',
                    'master_status',
                    'is_legacy',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('material_types', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Drop New Classification Masters
        |--------------------------------------------------------------------------
        */

        Schema::dropIfExists('material_product_types');
        Schema::dropIfExists('material_product_groups');
    }

    /**
     * Add an index only when it does not already exist.
     */
    private function addIndexIfMissing(
        string $table,
        string $indexName,
        array $columns
    ): void {
        $databaseName = DB::getDatabaseName();

        $exists = DB::table('information_schema.statistics')
            ->where('table_schema', $databaseName)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();

        if (! $exists) {
            Schema::table($table, function (Blueprint $blueprint) use (
                $indexName,
                $columns
            ) {
                $blueprint->index($columns, $indexName);
            });
        }
    }
};