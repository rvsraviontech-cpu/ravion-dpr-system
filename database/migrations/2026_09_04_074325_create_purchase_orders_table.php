<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number', 50)->unique();

            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete();

            $table->date('po_date');
            $table->date('expected_delivery_date')->nullable();
            $table->string('vendor_reference', 150)->nullable();

            // Commercial snapshots preserve the PO exactly as issued.
            $table->string('vendor_name', 255);
            $table->string('vendor_gst_number', 50)->nullable();
            $table->text('vendor_address')->nullable();
            $table->string('vendor_contact_person', 150)->nullable();
            $table->string('vendor_mobile', 30)->nullable();
            $table->string('vendor_email', 255)->nullable();

            $table->string('project_name', 255);
            $table->text('delivery_address')->nullable();

            $table->text('payment_terms')->nullable();
            $table->text('delivery_terms')->nullable();
            $table->text('freight_terms')->nullable();

            $table->char('currency', 3)->default('INR');

            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('discount_amount', 18, 2)->default(0);
            $table->decimal('taxable_amount', 18, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('other_charges', 18, 2)->default(0);
            $table->decimal('grand_total', 18, 2)->default(0);

            $table->string('status', 40)->default('Draft');

            $table->text('internal_remarks')->nullable();
            $table->text('vendor_notes')->nullable();
            $table->longText('terms_conditions')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();

            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();

            $table->index(['project_id', 'status', 'po_date'], 'purchase_orders_project_status_date_idx');
            $table->index(['vendor_id', 'status', 'po_date'], 'purchase_orders_vendor_status_date_idx');
            $table->index(['status', 'expected_delivery_date'], 'purchase_orders_status_delivery_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
