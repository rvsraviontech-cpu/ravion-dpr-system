<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_done_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_done_header_id')
                ->constrained('work_done_headers')
                ->cascadeOnDelete();

            $table->foreignId('dpr_id')
                ->nullable()
                ->constrained('dprs')
                ->nullOnDelete();

            $table->foreignId('work_stage_id')
                ->nullable()
                ->constrained('work_stages')
                ->nullOnDelete();

            $table->foreignId('activity_division_id')
                ->nullable()
                ->constrained('activity_divisions')
                ->nullOnDelete();

            $table->foreignId('activity_id')
                ->constrained('activities')
                ->restrictOnDelete();

            $table->foreignId('activity_mapping_id')
                ->nullable()
                ->constrained('activity_mappings')
                ->nullOnDelete();

            $table->foreignId('contractor_id')
                ->nullable()
                ->constrained('contractors')
                ->nullOnDelete();

            $table->foreignId('project_block_id')
                ->nullable()
                ->constrained('project_blocks')
                ->nullOnDelete();

            $table->foreignId('project_floor_id')
                ->nullable()
                ->constrained('project_floors')
                ->nullOnDelete();

            $table->foreignId('project_unit_id')
                ->nullable()
                ->constrained('project_units')
                ->nullOnDelete();

            $table->foreignId('project_room_id')
                ->nullable()
                ->constrained('project_rooms')
                ->nullOnDelete();

            $table->foreignId('project_subspace_id')
                ->nullable()
                ->constrained('project_subspaces')
                ->nullOnDelete();

            $table->decimal('quantity_completed', 14, 3);

            $table->string('unit', 50)
                ->nullable();

            $table->decimal('progress_percentage', 5, 2)
                ->nullable();

            $table->string('execution_status', 30)
                ->default('In Progress');

            $table->text('remarks')
                ->nullable();

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->timestamps();

            $table->index(
                ['work_done_header_id', 'sort_order'],
                'work_done_items_header_sort_index'
            );

            $table->index(
                ['dpr_id', 'activity_id'],
                'work_done_items_dpr_activity_index'
            );

            $table->index(
                ['activity_id', 'work_stage_id'],
                'work_done_items_activity_stage_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_done_items');
    }
};
