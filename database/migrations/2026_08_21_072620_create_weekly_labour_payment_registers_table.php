<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_labour_payment_registers', function (Blueprint $table): void {
            $table->id();
            $table->string('register_number', 50)->unique();
            $table->date('week_start_date');
            $table->date('week_end_date');
            $table->string('status', 30)->default('draft');

            $table->unsignedInteger('total_labours')->default(0);
            $table->decimal('total_full_days', 10, 2)->default(0);
            $table->decimal('total_half_days', 10, 2)->default(0);
            $table->decimal('total_payable_days', 10, 2)->default(0);

            $table->decimal('total_normal_wages', 14, 2)->default(0);
            $table->decimal('total_ot_hours', 12, 2)->default(0);
            $table->decimal('total_ot_wages', 14, 2)->default(0);
            $table->decimal('total_additions', 14, 2)->default(0);
            $table->decimal('total_deductions', 14, 2)->default(0);
            $table->decimal('gross_wages', 14, 2)->default(0);
            $table->decimal('net_payable', 14, 2)->default(0);

            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('payment_date')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_reference', 150)->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

           $table->index(
    ['week_start_date', 'week_end_date'],
    'wlpr_week_dates_idx'
);

$table->index(
    ['status', 'is_active'],
    'wlpr_status_active_idx'
);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_labour_payment_registers');
    }
};
