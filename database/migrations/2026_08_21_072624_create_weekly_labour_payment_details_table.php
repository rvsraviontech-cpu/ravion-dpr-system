<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_labour_payment_details', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger(
    'weekly_labour_payment_register_id'
);

$table->foreign(
    'weekly_labour_payment_register_id',
    'wlpd_register_fk'
)
    ->references('id')
    ->on('weekly_labour_payment_registers')
    ->cascadeOnDelete();

            $table->unsignedBigInteger('labour_id');

$table->foreign(
    'labour_id',
    'wlpd_labour_fk'
)
    ->references('id')
    ->on('labours')
    ->restrictOnDelete();


$table->unsignedBigInteger(
    'labour_group_id'
)->nullable();

$table->foreign(
    'labour_group_id',
    'wlpd_group_fk'
)
    ->references('id')
    ->on('labour_groups')
    ->nullOnDelete();


$table->unsignedBigInteger(
    'designation_role_id'
)->nullable();

$table->foreign(
    'designation_role_id',
    'wlpd_designation_fk'
)
    ->references('id')
    ->on('designation_roles')
    ->nullOnDelete();


$table->unsignedBigInteger(
    'labour_category_id'
)->nullable();

$table->foreign(
    'labour_category_id',
    'wlpd_category_fk'
)
    ->references('id')
    ->on('labour_categories')
    ->nullOnDelete();


$table->unsignedBigInteger(
    'contractor_id'
)->nullable();

$table->foreign(
    'contractor_id',
    'wlpd_contractor_fk'
)
    ->references('id')
    ->on('contractors')
    ->nullOnDelete();

            $table->decimal('full_days', 8, 2)->default(0);
            $table->decimal('half_days', 8, 2)->default(0);
            $table->decimal('payable_days', 8, 2)->default(0);
            $table->decimal('absent_days', 8, 2)->default(0);
            $table->decimal('leave_days', 8, 2)->default(0);
            $table->decimal('weekly_off_days', 8, 2)->default(0);
            $table->decimal('holiday_days', 8, 2)->default(0);

            $table->decimal('normal_hours', 10, 2)->default(0);
            $table->decimal('ot_hours', 10, 2)->default(0);

            $table->string('wage_basis', 30)->default('daily');
            $table->decimal('daily_wage_rate', 12, 2)->default(0);
            $table->decimal('standard_hours_per_day', 8, 2)->default(8);
            $table->decimal('ot_hourly_rate', 12, 4)->default(0);

            $table->decimal('normal_wage', 14, 2)->default(0);
            $table->decimal('ot_wage', 14, 2)->default(0);
            $table->decimal('gross_wage', 14, 2)->default(0);
            $table->decimal('additions', 14, 2)->default(0);
            $table->decimal('deductions', 14, 2)->default(0);
            $table->decimal('net_payable', 14, 2)->default(0);

            $table->text('adjustment_reason')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
    ['weekly_labour_payment_register_id', 'labour_id'],
    'wlpd_register_labour_uq'
);

$table->index(
    ['labour_group_id', 'labour_id'],
    'wlpd_group_labour_idx'
);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_labour_payment_details');
    }
};
