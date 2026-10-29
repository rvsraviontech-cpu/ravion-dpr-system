<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rfq_award_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained('rfqs')->restrictOnDelete();
            $table->foreignId('rfq_item_id')->constrained('rfq_items')->restrictOnDelete();
            $table->foreignId('rfq_quotation_item_id')->constrained('rfq_quotation_items')->restrictOnDelete();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->restrictOnDelete();
            $table->foreignId('purchase_order_item_id')->constrained('purchase_order_items')->restrictOnDelete();
            $table->decimal('awarded_quantity', 15, 3);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique('purchase_order_item_id');
            $table->index(['rfq_item_id', 'rfq_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_award_items');
    }
};
