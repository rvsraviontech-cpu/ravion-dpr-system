<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_consumed_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('material_consumed_id')
                ->constrained('material_consumeds')
                ->cascadeOnDelete();

            /*
             * Work classification.
             */
            $table->foreignId('activity_division_id')
                ->nullable()
                ->constrained('activity_divisions')
                ->nullOnDelete();

            $table->foreignId('activity_id')
                ->nullable()
                ->constrained('activities')
                ->nullOnDelete();

            /*
             * Material variant hierarchy.
             */
            $table->foreignId('material_type_id')
                ->constrained('material_types')
                ->restrictOnDelete();

            $table->foreignId('brand_master_id')
                ->nullable()
                ->constrained('brand_masters')
                ->nullOnDelete();

            $table->foreignId('material_specification_id')
                ->nullable()
                ->constrained('material_specifications')
                ->nullOnDelete();

            $table->foreignId('material_grade_id')
                ->nullable()
                ->constrained('material_grades')
                ->nullOnDelete();

            /*
             * Consumption quantities.
             */
            $table->decimal('quantity_consumed', 14, 3);

            $table->decimal('wastage_quantity', 14, 3)
                ->default(0);

            $table->foreignId('unit_master_id')
                ->constrained('unit_masters')
                ->restrictOnDelete();

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->text('wastage_reason')
                ->nullable();

            $table->text('remarks')
                ->nullable();

            $table->timestamps();

            $table->index(
                [
                    'material_consumed_id',
                    'sort_order',
                ],
                'material_consumed_items_header_sort_index'
            );

            $table->index(
                [
                    'material_type_id',
                    'brand_master_id',
                    'material_specification_id',
                    'material_grade_id',
                    'unit_master_id',
                ],
                'material_consumed_items_variant_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_consumed_items');
    }
};