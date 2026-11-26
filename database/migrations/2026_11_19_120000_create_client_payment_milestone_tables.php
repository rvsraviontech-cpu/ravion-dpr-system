<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_payment_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained('projects')->restrictOnDelete();
            $table->string('contract_reference', 100)->nullable();
            $table->date('agreement_date')->nullable();
            $table->char('currency', 3)->default('INR');
            $table->decimal('agreed_amount', 18, 2);
            $table->decimal('approved_variations', 18, 2)->default(0);
            $table->text('payment_terms')->nullable();
            $table->string('status', 30)->default('draft');
            $table->unsignedInteger('revision')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('client_payment_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('client_payment_contracts')->restrictOnDelete();
            $table->string('milestone_code', 50)->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 35); // advance, construction, additional, final, retention, custom
            $table->foreignId('work_stage_id')->nullable()->constrained('client_work_stages')->restrictOnDelete();
            $table->decimal('scheduled_amount', 18, 2);
            $table->decimal('scheduled_percent', 9, 4)->nullable();
            $table->date('planned_due_date')->nullable();
            $table->date('certified_on')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('revision')->default(1);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->index(['contract_id', 'status', 'sort_order']);
        });

        Schema::create('client_payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('client_payment_contracts')->restrictOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained('client_payment_milestones')->restrictOnDelete();
            $table->string('receipt_reference', 100)->nullable();
            $table->date('received_on');
            $table->decimal('amount', 18, 2);
            $table->string('payment_method', 40)->nullable();
            $table->string('transaction_reference', 150)->nullable();
            $table->text('remarks')->nullable();
            $table->string('status', 30)->default('draft'); // draft, verified, void
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['contract_id', 'received_on']);
        });

        Schema::create('client_payment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('client_payment_contracts')->restrictOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained('client_payment_milestones')->restrictOnDelete();
            $table->foreignId('receipt_id')->nullable()->constrained('client_payment_receipts')->restrictOnDelete();
            $table->string('actor_guard', 20)->default('web');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action', 70);
            $table->json('before_values')->nullable();
            $table->json('after_values')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['contract_id', 'created_at']);
        });

        Schema::create('client_payment_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('client_payment_contracts')->restrictOnDelete();
            $table->unsignedInteger('revision');
            $table->json('client_safe_snapshot');
            $table->string('status', 25)->default('draft'); // draft, approved, published, withdrawn
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();
            $table->unique(['contract_id', 'revision']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_payment_publications');
        Schema::dropIfExists('client_payment_events');
        Schema::dropIfExists('client_payment_receipts');
        Schema::dropIfExists('client_payment_milestones');
        Schema::dropIfExists('client_payment_contracts');
    }
};
