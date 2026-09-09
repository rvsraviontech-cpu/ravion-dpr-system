<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 2G.1
     *
     * Add receipt-source architecture to the existing Material Received
     * workflow without changing historical receipt records.
     */
    public function up(): void
    {
        Schema::table('material_receiveds', function (Blueprint $table) {

            $table->string('receipt_number', 50)
                ->nullable()
                ->after('id');

            $table->string('receipt_source', 30)
                ->nullable()
                ->after('receipt_number');

            $table->foreignId('purchase_order_id')
                ->nullable()
                ->after('receipt_source');

            $table->foreignId('material_dispatch_id')
                ->nullable()
                ->after('purchase_order_id');

            $table->foreignId('material_dispatch_receipt_id')
                ->nullable()
                ->after('material_dispatch_id');

            $table->string('source_reference', 100)
                ->nullable()
                ->after('material_dispatch_receipt_id');

            $table->decimal('subtotal', 15, 2)
                ->nullable()
                ->after('source_reference');

            $table->decimal('tax_amount', 15, 2)
                ->nullable()
                ->after('subtotal');

            $table->decimal('other_charges', 15, 2)
                ->nullable()
                ->after('tax_amount');

            $table->decimal('grand_total', 15, 2)
                ->nullable()
                ->after('other_charges');

            $table->unique(
                'receipt_number',
                'mr_receipt_number_unique'
            );

            $table->index(
                ['receipt_source', 'project_id', 'received_date'],
                'mr_source_project_date_idx'
            );

            $table->index(
                'purchase_order_id',
                'mr_purchase_order_idx'
            );

            $table->index(
                'material_dispatch_id',
                'mr_dispatch_idx'
            );

            $table->foreign(
                'purchase_order_id',
                'mr_purchase_order_fk'
            )
                ->references('id')
                ->on('purchase_orders')
                ->nullOnDelete();

            $table->foreign(
                'material_dispatch_id',
                'mr_dispatch_fk'
            )
                ->references('id')
                ->on('material_dispatches')
                ->nullOnDelete();

            $table->foreign(
                'material_dispatch_receipt_id',
                'mr_dispatch_receipt_fk'
            )
                ->references('id')
                ->on('material_dispatch_receipts')
                ->nullOnDelete();
        });

        Schema::table('material_received_items', function (Blueprint $table) {

            $table->foreignId('purchase_order_item_id')
                ->nullable()
                ->after('material_received_id');

            $table->foreignId('purchase_order_item_allocation_id')
                ->nullable()
                ->after('purchase_order_item_id');

            $table->foreign(
                'purchase_order_item_id',
                'mri_po_item_fk'
            )
                ->references('id')
                ->on('purchase_order_items')
                ->nullOnDelete();

            $table->foreign(
                'purchase_order_item_allocation_id',
                'mri_po_alloc_fk'
            )
                ->references('id')
                ->on('purchase_order_item_allocations')
                ->nullOnDelete();

            $table->decimal('rate', 15, 4)
                ->nullable()
                ->after('unit_master_id');

            $table->decimal('discount_amount', 15, 2)
                ->nullable()
                ->after('rate');

            $table->decimal('tax_percent', 8, 4)
                ->nullable()
                ->after('discount_amount');

            $table->decimal('tax_amount', 15, 2)
                ->nullable()
                ->after('tax_percent');

            $table->decimal('line_amount', 15, 2)
                ->nullable()
                ->after('tax_amount');
        });
    }

    /**
     * Reverse Phase 2G.1.
     */
    public function down(): void
    {
        Schema::table('material_received_items', function (Blueprint $table) {

            $table->dropForeign('mri_po_alloc_fk');
            $table->dropForeign('mri_po_item_fk');

            $table->dropColumn([
                'purchase_order_item_allocation_id',
                'purchase_order_item_id',
                'rate',
                'discount_amount',
                'tax_percent',
                'tax_amount',
                'line_amount',
            ]);
        });

        Schema::table('material_receiveds', function (Blueprint $table) {

            $table->dropForeign('mr_dispatch_receipt_fk');
            $table->dropForeign('mr_dispatch_fk');
            $table->dropForeign('mr_purchase_order_fk');

            $table->dropIndex('mr_dispatch_idx');
            $table->dropIndex('mr_purchase_order_idx');
            $table->dropIndex('mr_source_project_date_idx');
            $table->dropUnique('mr_receipt_number_unique');

            $table->dropColumn([
                'receipt_number',
                'receipt_source',
                'purchase_order_id',
                'material_dispatch_id',
                'material_dispatch_receipt_id',
                'source_reference',
                'subtotal',
                'tax_amount',
                'other_charges',
                'grand_total',
            ]);
        });
    }
};