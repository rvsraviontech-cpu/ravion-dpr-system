<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_consumeds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dpr_id')->nullable()->constrained('dprs')->nullOnDelete();

            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->foreignId('project_block_id')->nullable()->constrained('project_blocks')->nullOnDelete();
            $table->foreignId('project_floor_id')->nullable()->constrained('project_floors')->nullOnDelete();
            $table->foreignId('project_unit_id')->nullable()->constrained('project_units')->nullOnDelete();
            $table->foreignId('project_room_id')->nullable()->constrained('project_rooms')->nullOnDelete();
            $table->foreignId('project_subspace_id')->nullable()->constrained('project_subspaces')->nullOnDelete();

            $table->foreignId('activity_division_id')->nullable()->constrained('activity_divisions')->nullOnDelete();
            $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete();
            $table->foreignId('activity_mapping_id')->nullable()->constrained('activity_mappings')->nullOnDelete();

            $table->foreignId('material_category_id')->nullable()->constrained('material_categories')->nullOnDelete();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();

            $table->foreignId('contractor_id')->nullable()->constrained('contractors')->nullOnDelete();

            $table->decimal('quantity_consumed', 12, 2)->default(0);
            $table->string('unit')->nullable();

            $table->decimal('related_work_output_quantity', 12, 2)->default(0);

            $table->decimal('wastage_quantity', 12, 2)->default(0);
            $table->string('wastage_reason')->nullable();

            $table->date('consumed_date');
            $table->time('consumed_time')->nullable();

            $table->string('status')->default('Draft');

            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_consumeds');
    }
};