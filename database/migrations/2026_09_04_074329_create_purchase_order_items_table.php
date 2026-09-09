<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_order_id')
                ->constrained('purchase_orders')
                ->cascadeOnDelete();

            $table->foreignId('material_type_id')
                ->nullable()
                ->constrained('material_types')
                ->nullOnDelete();

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
                ->nullable()
                ->constrained('unit_masters')
                ->nullOnDelete();

            // Snapshot fields preserve exactly what the vendor was ordered.
            $table->string('product_name', 255);
            $table->string('product_code', 100)->nullable();
            $table->string('specification_text', 500)->nullable();
            $table->string('brand_name', 255)->nullable();
            $table->string('unit_name', 100)->nullable();
            $table->string('unit_code', 50)->nullable();

            $table->decimal('ordered_quantity', 14, 3);
            $table->decimal('received_quantity', 14, 3)->default(0);

            $table->decimal('rate', 18, 4)->default(0);

            $table->decimal('discount_percent', 8, 4)->default(0);
            $table->decimal('discount_amount', 18, 2)->default(0);

            $table->decimal('taxable_amount', 18, 2)->default(0);
            $table->decimal('tax_percent', 8, 4)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);

            $table->decimal('line_amount', 18, 2)->default(0);

            $table->unsignedInteger('sort_order')->default(0);
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['purchase_order_id', 'sort_order'], 'purchase_order_items_po_sort_idx');
            $table->index(
                ['material_type_id', 'brand_master_id', 'unit_master_id'],
                'purchase_order_items_product_brand_unit_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
