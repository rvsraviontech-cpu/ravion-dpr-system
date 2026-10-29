<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->string('rfq_number', 40)->unique();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->date('rfq_date');
            $table->dateTime('submission_deadline')->nullable();
            $table->string('status', 30)->default('Draft')->index();
            $table->text('delivery_address')->nullable();
            $table->string('delivery_contact_name')->nullable();
            $table->string('delivery_contact_phone', 40)->nullable();
            $table->text('vendor_instructions')->nullable();
            $table->text('internal_remarks')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('rfq_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained('rfqs')->cascadeOnDelete();
            $table->foreignId('material_requirement_item_id')->nullable()->constrained('material_requirement_items')->nullOnDelete();
            $table->foreignId('material_type_id')->constrained('material_types')->restrictOnDelete();
            $table->foreignId('material_variant_id')->nullable()->constrained('material_variants')->restrictOnDelete();
            $table->foreignId('brand_master_id')->nullable()->constrained('brand_masters')->restrictOnDelete();
            $table->foreignId('material_specification_id')->nullable()->constrained('material_specifications')->restrictOnDelete();
            $table->foreignId('material_grade_id')->nullable()->constrained('material_grades')->restrictOnDelete();
            $table->foreignId('unit_master_id')->constrained('unit_masters')->restrictOnDelete();
            $table->string('product_name');
            $table->text('specification_text')->nullable();
            $table->decimal('requested_quantity', 16, 3);
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->index(['rfq_id', 'sort_order']);
        });

        Schema::create('rfq_vendor_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained('rfqs')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->string('status', 30)->default('Pending')->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->unique(['rfq_id', 'vendor_id']);
        });

        Schema::create('rfq_invitation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_vendor_invitation_id', 'rfq_invitation_fk')->constrained('rfq_vendor_invitations')->cascadeOnDelete();
            $table->foreignId('rfq_item_id')->constrained('rfq_items')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['rfq_vendor_invitation_id', 'rfq_item_id'], 'rfq_inv_item_unique');
        });

        Schema::create('rfq_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_vendor_invitation_id', 'rfq_quote_inv_fk')->constrained('rfq_vendor_invitations')->restrictOnDelete();
            $table->unsignedInteger('revision_number')->default(1);
            $table->string('status', 30)->default('Draft');
            $table->date('valid_until')->nullable();
            $table->text('payment_terms')->nullable();
            $table->text('delivery_terms')->nullable();
            $table->text('vendor_remarks')->nullable();
            $table->decimal('product_amount', 18, 2)->nullable();
            $table->decimal('tax_amount', 18, 2)->nullable();
            $table->decimal('other_charges', 18, 2)->nullable();
            $table->decimal('net_total_amount', 18, 2)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->unique(['rfq_vendor_invitation_id', 'revision_number'], 'rfq_quote_revision_unique');
        });

        Schema::create('rfq_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_quotation_id')->constrained('rfq_quotations')->cascadeOnDelete();
            $table->foreignId('rfq_item_id')->constrained('rfq_items')->restrictOnDelete();
            $table->decimal('offered_quantity', 16, 3)->nullable();
            $table->decimal('rate', 18, 4)->nullable();
            $table->decimal('discount_amount', 18, 2)->nullable();
            $table->decimal('tax_percent', 7, 3)->nullable();
            $table->decimal('product_amount', 18, 2)->nullable();
            $table->decimal('tax_amount', 18, 2)->nullable();
            $table->decimal('line_total', 18, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['rfq_quotation_id', 'rfq_item_id']);
        });

        Schema::create('rfq_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_item_id')->constrained('rfq_items')->restrictOnDelete();
            $table->foreignId('rfq_quotation_item_id')->nullable()->constrained('rfq_quotation_items')->restrictOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete();
            $table->decimal('awarded_quantity', 16, 3);
            $table->decimal('agreed_rate', 18, 4)->nullable();
            $table->foreignId('purchase_order_item_id')->nullable()->constrained('purchase_order_items')->restrictOnDelete();
            $table->foreignId('awarded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('awarded_at');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('vendor_material_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('material_type_id')->constrained('material_types')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['vendor_id', 'material_type_id']);
        });
    }

    public function down(): void
    {
        foreach (['vendor_material_mappings', 'rfq_awards', 'rfq_quotation_items', 'rfq_quotations', 'rfq_invitation_items', 'rfq_vendor_invitations', 'rfq_items', 'rfqs'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
