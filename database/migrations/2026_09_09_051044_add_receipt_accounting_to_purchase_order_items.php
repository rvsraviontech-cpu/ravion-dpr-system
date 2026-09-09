<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 2G.2
     *
     * Add cumulative receipt-accounting quantities to Purchase Order items
     * and their Material Requirement allocations.
     *
     * Quantity semantics:
     *
     * received_quantity = quantity physically received at site
     *
     * accepted_quantity = physically received and accepted
     * damaged_quantity  = physically received but damaged
     * rejected_quantity = physically received but rejected
     * short_quantity    = quantity expected but not physically delivered
     *
     * Therefore:
     *
     * received = accepted + damaged + rejected
     * accounted = received + short
     * pending = ordered - accounted
     *
     * Only accepted quantity becomes stock eligible.
     */
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('accepted_quantity', 14, 3)
                ->default(0)
                ->after('received_quantity');

            $table->decimal('short_quantity', 14, 3)
                ->default(0)
                ->after('accepted_quantity');

            $table->decimal('damaged_quantity', 14, 3)
                ->default(0)
                ->after('short_quantity');

            $table->decimal('rejected_quantity', 14, 3)
                ->default(0)
                ->after('damaged_quantity');
        });

        Schema::table('purchase_order_item_allocations', function (Blueprint $table) {
            $table->decimal('accepted_quantity', 14, 3)
                ->default(0)
                ->after('received_quantity');

            $table->decimal('short_quantity', 14, 3)
                ->default(0)
                ->after('accepted_quantity');

            $table->decimal('damaged_quantity', 14, 3)
                ->default(0)
                ->after('short_quantity');

            $table->decimal('rejected_quantity', 14, 3)
                ->default(0)
                ->after('damaged_quantity');
        });
    }

    /**
     * Reverse Phase 2G.2.
     */
    public function down(): void
    {
        Schema::table('purchase_order_item_allocations', function (Blueprint $table) {
            $table->dropColumn([
                'accepted_quantity',
                'short_quantity',
                'damaged_quantity',
                'rejected_quantity',
            ]);
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn([
                'accepted_quantity',
                'short_quantity',
                'damaged_quantity',
                'rejected_quantity',
            ]);
        });
    }
};