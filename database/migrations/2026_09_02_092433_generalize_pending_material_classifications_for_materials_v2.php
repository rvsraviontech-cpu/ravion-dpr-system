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
        if (! Schema::hasTable('pending_material_classifications')) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Generalize Pending Material Classification
        |--------------------------------------------------------------------------
        |
        | Existing architecture was primarily tied to Material Received.
        |
        | Materials V2 uses this table as the common Pending Master Review /
        | Temporary Material identity for:
        |
        | - Material Required
        | - Material Received
        | - Material Consumed
        | - Head Office Dispatch
        | - Purchase Order (where required)
        | - Future inventory transactions
        |
        | Existing columns and existing records are intentionally preserved.
        |
        */

        Schema::table('pending_material_classifications', function (Blueprint $table) {
            /*
            |--------------------------------------------------------------------------
            | Temporary Material Identity
            |--------------------------------------------------------------------------
            */

            if (! Schema::hasColumn(
                'pending_material_classifications',
                'temporary_material_code'
            )) {
                $table->string('temporary_material_code', 50)
                    ->nullable()
                    ->after('id');
            }

            /*
            |--------------------------------------------------------------------------
            | Generic Source Transaction
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | source_module    = Material Received
            | source_table     = material_received_items
            | source_header_id = 18
            | source_item_id   = 20
            |
            | We intentionally keep the old material_received_id column for
            | historical compatibility.
            |
            */

            if (! Schema::hasColumn(
                'pending_material_classifications',
                'source_module'
            )) {
                $table->string('source_module', 100)
                    ->nullable()
                    ->after('temporary_material_code');
            }

            if (! Schema::hasColumn(
                'pending_material_classifications',
                'source_table'
            )) {
                $table->string('source_table', 100)
                    ->nullable()
                    ->after('source_module');
            }

            if (! Schema::hasColumn(
                'pending_material_classifications',
                'source_header_id'
            )) {
                $table->unsignedBigInteger('source_header_id')
                    ->nullable()
                    ->after('source_table');
            }

            if (! Schema::hasColumn(
                'pending_material_classifications',
                'source_item_id'
            )) {
                $table->unsignedBigInteger('source_item_id')
                    ->nullable()
                    ->after('source_header_id');
            }

            /*
            |--------------------------------------------------------------------------
            | Raw Engineer-entered Unit
            |--------------------------------------------------------------------------
            |
            | unit_master_id already exists and remains the normalized/raw
            | selected Unit reference.
            |
            | raw_unit preserves the exact user wording when needed.
            |
            */

            if (! Schema::hasColumn(
                'pending_material_classifications',
                'raw_unit'
            )) {
                $table->string('raw_unit', 100)
                    ->nullable()
                    ->after('raw_grade');
            }

            /*
            |--------------------------------------------------------------------------
            | Resolution Mapping
            |--------------------------------------------------------------------------
            |
            | mapped_material_type_id already exists.
            |
            | These new fields allow PMO/Admin to resolve the temporary material
            | into the complete canonical Product identity.
            |
            */

            if (! Schema::hasColumn(
                'pending_material_classifications',
                'mapped_material_variant_id'
            )) {
                $table->unsignedBigInteger('mapped_material_variant_id')
                    ->nullable()
                    ->after('mapped_material_type_id');
            }

            if (! Schema::hasColumn(
                'pending_material_classifications',
                'mapped_brand_master_id'
            )) {
                $table->unsignedBigInteger('mapped_brand_master_id')
                    ->nullable()
                    ->after('mapped_material_variant_id');
            }

            if (! Schema::hasColumn(
                'pending_material_classifications',
                'mapped_unit_master_id'
            )) {
                $table->unsignedBigInteger('mapped_unit_master_id')
                    ->nullable()
                    ->after('mapped_brand_master_id');
            }

            /*
            |--------------------------------------------------------------------------
            | Resolution Notes
            |--------------------------------------------------------------------------
            */

            if (! Schema::hasColumn(
                'pending_material_classifications',
                'resolution_notes'
            )) {
                $table->text('resolution_notes')
                    ->nullable()
                    ->after('resolved_at');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Foreign Keys
        |--------------------------------------------------------------------------
        |
        | Explicit short names avoid MySQL's 64-character identifier limit.
        |
        */

        $this->addForeignKeyIfMissing(
            'pending_material_classifications',
            'pmc_variant_fk',
            'mapped_material_variant_id',
            'material_variants',
            'id'
        );

        $this->addForeignKeyIfMissing(
            'pending_material_classifications',
            'pmc_brand_fk',
            'mapped_brand_master_id',
            'brand_masters',
            'id'
        );

        $this->addForeignKeyIfMissing(
            'pending_material_classifications',
            'pmc_mapped_unit_fk',
            'mapped_unit_master_id',
            'unit_masters',
            'id'
        );

        /*
        |--------------------------------------------------------------------------
        | Indexes
        |--------------------------------------------------------------------------
        */

        $this->addIndexIfMissing(
            'pending_material_classifications',
            'pmc_temp_code_idx',
            ['temporary_material_code']
        );

        $this->addIndexIfMissing(
            'pending_material_classifications',
            'pmc_source_idx',
            [
                'source_module',
                'source_header_id',
                'source_item_id',
            ]
        );

        $this->addIndexIfMissing(
            'pending_material_classifications',
            'pmc_status_project_idx',
            [
                'status',
                'project_id',
            ]
        );

        $this->addIndexIfMissing(
            'pending_material_classifications',
            'pmc_resolution_idx',
            [
                'mapped_material_type_id',
                'mapped_material_variant_id',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Backfill Existing Pending Records
        |--------------------------------------------------------------------------
        |
        | Existing rows were created by Material Received.
        |
        | We preserve their old material_received_id relationship while also
        | populating the generic source metadata.
        |
        */

        DB::table('pending_material_classifications')
            ->whereNull('source_module')
            ->whereNotNull('material_received_id')
            ->update([
                'source_module' => 'Material Received',
                'source_table' => 'material_receiveds',
                'source_header_id' => DB::raw('material_received_id'),
            ]);

        /*
        |--------------------------------------------------------------------------
        | Temporary Codes for Existing Records
        |--------------------------------------------------------------------------
        |
        | Each unresolved/manual material receives a stable temporary identity.
        |
        | Example:
        | TEMP-000001
        | TEMP-000002
        |
        */

        $rows = DB::table('pending_material_classifications')
            ->whereNull('temporary_material_code')
            ->orderBy('id')
            ->get(['id']);

        foreach ($rows as $row) {
            DB::table('pending_material_classifications')
                ->where('id', $row->id)
                ->update([
                    'temporary_material_code' =>
                        'TEMP-' . str_pad(
                            (string) $row->id,
                            6,
                            '0',
                            STR_PAD_LEFT
                        ),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('pending_material_classifications')) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Remove New Foreign Keys
        |--------------------------------------------------------------------------
        */

        $this->dropForeignKeyIfExists(
            'pending_material_classifications',
            'pmc_variant_fk'
        );

        $this->dropForeignKeyIfExists(
            'pending_material_classifications',
            'pmc_brand_fk'
        );

        $this->dropForeignKeyIfExists(
            'pending_material_classifications',
            'pmc_mapped_unit_fk'
        );

        /*
        |--------------------------------------------------------------------------
        | Remove Materials V2 Columns
        |--------------------------------------------------------------------------
        |
        | Existing legacy columns and records remain untouched.
        |
        */

        Schema::table('pending_material_classifications', function (Blueprint $table) {
            $columns = [
                'temporary_material_code',
                'source_module',
                'source_table',
                'source_header_id',
                'source_item_id',
                'raw_unit',
                'mapped_material_variant_id',
                'mapped_brand_master_id',
                'mapped_unit_master_id',
                'resolution_notes',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn(
                    'pending_material_classifications',
                    $column
                )) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Add a foreign key only when the named constraint does not exist.
     */
    private function addForeignKeyIfMissing(
        string $table,
        string $constraintName,
        string $column,
        string $referencedTable,
        string $referencedColumn
    ): void {
        $exists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraintName)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();

        if ($exists) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use (
            $constraintName,
            $column,
            $referencedTable,
            $referencedColumn
        ) {
            $blueprint->foreign($column, $constraintName)
                ->references($referencedColumn)
                ->on($referencedTable)
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Drop a foreign key only when it exists.
     */
    private function dropForeignKeyIfExists(
        string $table,
        string $constraintName
    ): void {
        $exists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraintName)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();

        if (! $exists) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use (
            $constraintName
        ) {
            $blueprint->dropForeign($constraintName);
        });
    }

    /**
     * Add an index only when it does not already exist.
     */
    private function addIndexIfMissing(
        string $table,
        string $indexName,
        array $columns
    ): void {
        $exists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();

        if ($exists) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use (
            $indexName,
            $columns
        ) {
            $blueprint->index($columns, $indexName);
        });
    }
};