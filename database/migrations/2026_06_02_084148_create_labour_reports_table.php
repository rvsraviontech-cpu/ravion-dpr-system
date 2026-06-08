<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labour_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dpr_id')
                ->nullable()
                ->constrained('dprs')
                ->nullOnDelete();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

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

            $table->foreignId('activity_id')
                ->nullable()
                ->constrained('activities')
                ->nullOnDelete();

            $table->foreignId('activity_mapping_id')
                ->nullable()
                ->constrained('activity_mappings')
                ->nullOnDelete();

            $table->foreignId('contractor_id')
                ->nullable()
                ->constrained('contractors')
                ->nullOnDelete();

            $table->integer('skilled_count')->default(0);
            $table->integer('semi_skilled_count')->default(0);
            $table->integer('helper_count')->default(0);
            $table->integer('semi_helper_count')->default(0);
            $table->integer('supervisor_count')->default(0);
            $table->integer('technician_count')->default(0);
            $table->integer('machine_operator_count')->default(0);

            $table->integer('male_count')->default(0);
            $table->integer('female_count')->default(0);
            $table->integer('local_count')->default(0);
            $table->integer('non_local_count')->default(0);

            $table->integer('total_labour')->default(0);

            $table->string('shift')->nullable();
            $table->decimal('work_output', 12, 2)->nullable();
            $table->string('work_output_unit')->nullable();

            $table->date('entry_date');
            $table->time('entry_time')->nullable();

            $table->string('status')->default('Draft');

            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labour_reports');
    }
};