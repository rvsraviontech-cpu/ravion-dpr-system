<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_dispatch_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('material_dispatch_id')
                ->constrained('material_dispatches')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Product Identity
            |--------------------------------------------------------------------------
            */
            $table->foreignId('material_type_id')
                ->constrained('material_types')
                ->restrictOnDelete();

            $table->foreignId('material_specification_id')
                ->nullable()
                ->constrained('material_specifications')
                ->nullOnDelete();

            $table->foreignId('material_grade_id')
                ->nullable()
                ->constrained('material_grades')
                ->nullOnDelete();

            $table->foreignId('brand_master_id')
                ->nullable()
                ->constrained('brand_masters')
                ->nullOnDelete();

            $table->foreignId('unit_master_id')
                ->constrained('unit_masters')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Snapshot Fields
            |--------------------------------------------------------------------------
            |
            | Preserve exactly what was dispatched even if Masters change later.
            |
            */
            $table->string('product_name', 500);
            $table->string('product_code', 100)->nullable();

            $table->string('specification_text', 500)->nullable();
            $table->string('brand_name', 255)->nullable();

            $table->string('unit_name', 100)->nullable();
            $table->string('unit_code', 50)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Quantity Tracking
            |--------------------------------------------------------------------------
            */
            $table->decimal('dispatched_quantity', 14, 3);

            $table->decimal('received_quantity', 14, 3)
                ->default(0);

            $table->decimal('accepted_quantity', 14, 3)
                ->default(0);

            $table->decimal('short_quantity', 14, 3)
                ->default(0);

            $table->decimal('damaged_quantity', 14, 3)
                ->default(0);

            $table->decimal('rejected_quantity', 14, 3)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Other
            |--------------------------------------------------------------------------
            */
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(
                ['material_dispatch_id', 'sort_order'],
                'material_dispatch_items_header_sort_index'
            );

            $table->index(
                [
                    'material_type_id',
                    'brand_master_id',
                    'unit_master_id'
                ],
                'material_dispatch_items_product_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_dispatch_items');
    }
};