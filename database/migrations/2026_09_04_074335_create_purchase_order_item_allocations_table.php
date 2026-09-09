<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_item_allocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_order_item_id')
                ->constrained('purchase_order_items')
                ->cascadeOnDelete();

            $table->foreignId('material_requirement_item_id')
                ->constrained('material_requirement_items')
                ->restrictOnDelete();

            $table->decimal('allocated_quantity', 14, 3);
            $table->decimal('received_quantity', 14, 3)->default(0);

            $table->timestamps();

            $table->unique(
                ['purchase_order_item_id', 'material_requirement_item_id'],
                'po_item_requirement_item_unique'
            );

            $table->index(
                ['material_requirement_item_id', 'purchase_order_item_id'],
                'po_alloc_requirement_item_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_item_allocations');
    }
};
