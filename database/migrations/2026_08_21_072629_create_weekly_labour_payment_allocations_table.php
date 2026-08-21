<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_labour_payment_allocations', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger(
    'weekly_labour_payment_detail_id'
);

$table->foreign(
    'weekly_labour_payment_detail_id',
    'wlpa_detail_fk'
)
    ->references('id')
    ->on('weekly_labour_payment_details')
    ->cascadeOnDelete();


$table->unsignedBigInteger(
    'project_id'
)->nullable();

$table->foreign(
    'project_id',
    'wlpa_project_fk'
)
    ->references('id')
    ->on('projects')
    ->nullOnDelete();

            $table->string('project_name', 255);
            $table->string('project_code', 100)->nullable();

            $table->decimal('full_days', 8, 2)->default(0);
            $table->decimal('half_days', 8, 2)->default(0);
            $table->decimal('payable_days', 8, 2)->default(0);

            $table->decimal('normal_hours', 10, 2)->default(0);
            $table->decimal('ot_hours', 10, 2)->default(0);

            $table->decimal('normal_wage', 14, 2)->default(0);
            $table->decimal('ot_wage', 14, 2)->default(0);
            $table->decimal('total_wage', 14, 2)->default(0);

            $table->json('attendance_dates')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
    ['weekly_labour_payment_detail_id', 'project_id'],
    'wlpa_detail_project_uq'
);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_labour_payment_allocations');
    }
};
