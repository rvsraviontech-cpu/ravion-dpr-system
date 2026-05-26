<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_mappings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('activity_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('division_code')
                ->default('RH');

            $table->string('rh_cost_code')
                ->nullable()
                ->index();

            $table->string('odoo_type_code')
                ->nullable();

            $table->string('odoo_type')
                ->nullable();

            $table->string('unit')
                ->nullable();

            $table->string('project_type')
                ->nullable();

            $table->string('structure_type')
                ->nullable();

            $table->string('work_stage')
                ->nullable();

            $table->string('activity_name');

            $table->string('boq_item_id')
                ->nullable();

            $table->string('material_group')
                ->nullable();

            $table->string('contractor_type')
                ->nullable();

            $table->decimal('productivity_norm', 12, 2)
                ->nullable();

            $table->string('quality_checklist_id')
                ->nullable();

            $table->string('odoo_analytic_account_code')
                ->nullable();

            $table->string('odoo_analytic_tag_code')
                ->nullable();

            $table->string('inventory_expense_bucket')
                ->nullable();

            $table->string('procurement_mode')
                ->nullable();

            $table->boolean('is_active')
                ->default(true);

            $table->text('remarks')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'division_code',
                'rh_cost_code'
            ], 'activity_mappings_division_cost_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_mappings');
    }
};