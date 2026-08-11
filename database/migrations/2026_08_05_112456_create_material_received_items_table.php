<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_received_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('material_received_id')
                ->constrained('material_receiveds')
                ->cascadeOnDelete();

            /*
             * Work classification shown to the Engineer.
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
             * Reusable material hierarchy.
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

            $table->decimal('quantity_received', 14, 3);

            $table->foreignId('unit_master_id')
                ->constrained('unit_masters')
                ->restrictOnDelete();

            /*
             * Verification is item-specific because one delivery can contain
             * accepted, damaged or short quantities for different materials.
             */
            $table->decimal('accepted_quantity', 14, 3)
                ->default(0);

            $table->decimal('short_quantity', 14, 3)
                ->default(0);

            $table->decimal('damaged_quantity', 14, 3)
                ->default(0);

            $table->decimal('rejected_quantity', 14, 3)
                ->default(0);

            $table->string('material_condition')
                ->default('Pending Verification');

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->text('remarks')
                ->nullable();

            $table->timestamps();

            $table->index(
                [
                    'material_received_id',
                    'sort_order',
                ],
                'material_received_items_receipt_sort_index'
            );

            $table->index(
                [
                    'material_type_id',
                    'brand_master_id',
                    'material_specification_id',
                    'material_grade_id',
                ],
                'material_received_items_variant_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_received_items');
    }
};