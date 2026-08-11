<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_requirement_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('material_requirement_id')
                ->constrained('material_requirements')
                ->cascadeOnDelete();

            $table->foreignId('activity_division_id')
                ->nullable()
                ->constrained('activity_divisions')
                ->nullOnDelete();

            $table->foreignId('activity_id')
                ->nullable()
                ->constrained('activities')
                ->nullOnDelete();

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

            $table->decimal('required_quantity', 14, 3);

            $table->decimal('fulfilled_quantity', 14, 3)
                ->default(0);

            $table->foreignId('unit_master_id')
                ->constrained('unit_masters')
                ->restrictOnDelete();

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->text('remarks')
                ->nullable();

            $table->timestamps();

            $table->index(
                [
                    'material_requirement_id',
                    'sort_order',
                ],
                'material_requirement_items_header_sort_index'
            );

            $table->index(
                [
                    'material_type_id',
                    'brand_master_id',
                    'material_specification_id',
                    'material_grade_id',
                    'unit_master_id',
                ],
                'material_requirement_items_variant_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_requirement_items');
    }
};