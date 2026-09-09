<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_dispatch_receipt_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('material_dispatch_receipt_id');

            $table->foreignId('material_dispatch_item_id');

            /*
             * Product snapshot / references.
             *
             * The dispatch item remains the primary source reference,
             * while these fields preserve what was actually received.
             */
            $table->foreignId('material_type_id')
                ->constrained('material_types')
                ->restrictOnDelete();

            $table->foreignId('brand_master_id')
                ->nullable()
                ->constrained('brand_masters')
                ->nullOnDelete();

            $table->foreignId('unit_master_id')
                ->constrained('unit_masters')
                ->restrictOnDelete();

            $table->string('product_name', 500);
            $table->string('product_code', 100)->nullable();
            $table->string('specification_text', 500)->nullable();
            $table->string('brand_name', 255)->nullable();
            $table->string('unit_name', 100)->nullable();
            $table->string('unit_code', 50)->nullable();

            /*
             * Quantity snapshot before this receipt.
             */
            $table->decimal('dispatched_quantity', 14, 3);
            $table->decimal('previously_received_quantity', 14, 3)
                ->default(0);

            /*
             * This receipt event.
             *
             * received_quantity is the physical quantity that arrived.
             *
             * received_quantity =
             * accepted_quantity +
             * damaged_quantity +
             * rejected_quantity
             *
             * short_quantity is not included in received_quantity because
             * short material did not physically arrive at site.
             */
            $table->decimal('received_quantity', 14, 3)->default(0);
            $table->decimal('accepted_quantity', 14, 3)->default(0);
            $table->decimal('short_quantity', 14, 3)->default(0);
            $table->decimal('damaged_quantity', 14, 3)->default(0);
            $table->decimal('rejected_quantity', 14, 3)->default(0);

            $table->text('remarks')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            /*
             * Explicit short FK/index names avoid MySQL's
             * 64-character identifier limit.
             */
            $table->foreign(
                'material_dispatch_receipt_id',
                'mdri_receipt_fk'
            )
                ->references('id')
                ->on('material_dispatch_receipts')
                ->cascadeOnDelete();

            $table->foreign(
                'material_dispatch_item_id',
                'mdri_dispatch_item_fk'
            )
                ->references('id')
                ->on('material_dispatch_items')
                ->restrictOnDelete();

            /*
             * One dispatch item can appear only once in one receipt,
             * but can appear again in later receipts for partial receipt.
             */
            $table->unique(
                [
                    'material_dispatch_receipt_id',
                    'material_dispatch_item_id',
                ],
                'mdri_receipt_dispatch_item_unique'
            );

            $table->index(
                ['material_dispatch_item_id', 'material_dispatch_receipt_id'],
                'mdri_dispatch_receipt_idx'
            );

            $table->index(
                ['material_type_id', 'unit_master_id'],
                'mdri_product_unit_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_dispatch_receipt_items');
    }
};