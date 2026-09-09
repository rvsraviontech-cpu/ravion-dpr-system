<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_material_classifications', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('material_received_id');
            $table->unsignedBigInteger('project_id');

            $table->string('raw_material_name', 255);
            $table->string('raw_brand', 255)->nullable();
            $table->string('raw_specification', 255)->nullable();
            $table->string('raw_grade', 255)->nullable();

            $table->unsignedBigInteger('unit_master_id')->nullable();
            $table->unsignedBigInteger('suggested_work_package_id')->nullable();

            $table->string('status', 30)->default('Pending');

            $table->unsignedBigInteger('mapped_material_type_id')->nullable();
            $table->unsignedBigInteger('mapped_work_package_id')->nullable();

            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('material_received_id', 'pmc_receipt_fk')
                ->references('id')
                ->on('material_receiveds')
                ->cascadeOnDelete();

            $table->foreign('project_id', 'pmc_project_fk')
                ->references('id')
                ->on('projects');

            $table->foreign('unit_master_id', 'pmc_unit_fk')
                ->references('id')
                ->on('unit_masters')
                ->nullOnDelete();

            $table->foreign('suggested_work_package_id', 'pmc_suggested_wp_fk')
                ->references('id')
                ->on('construction_work_packages')
                ->nullOnDelete();

            $table->foreign('mapped_material_type_id', 'pmc_mapped_type_fk')
                ->references('id')
                ->on('material_types')
                ->nullOnDelete();

            $table->foreign('mapped_work_package_id', 'pmc_mapped_wp_fk')
                ->references('id')
                ->on('construction_work_packages')
                ->nullOnDelete();

            $table->foreign('requested_by', 'pmc_requested_by_fk')
                ->references('id')
                ->on('users');

            $table->foreign('resolved_by', 'pmc_resolved_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(
                ['status', 'project_id'],
                'pmc_status_project_idx'
            );

            $table->index(
                ['material_received_id', 'status'],
                'pmc_receipt_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_material_classifications');
    }
};
