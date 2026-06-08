<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dpr_work_items', function (Blueprint $table) {
            $table->foreignId('project_block_id')
                ->nullable()
                ->after('activity_id')
                ->constrained('project_blocks')
                ->nullOnDelete();

            $table->foreignId('project_floor_id')
                ->nullable()
                ->after('project_block_id')
                ->constrained('project_floors')
                ->nullOnDelete();

            $table->foreignId('project_unit_id')
                ->nullable()
                ->after('project_floor_id')
                ->constrained('project_units')
                ->nullOnDelete();

            $table->foreignId('project_room_id')
                ->nullable()
                ->after('project_unit_id')
                ->constrained('project_rooms')
                ->nullOnDelete();

            $table->foreignId('project_subspace_id')
                ->nullable()
                ->after('project_room_id')
                ->constrained('project_subspaces')
                ->nullOnDelete();

            $table->foreignId('activity_mapping_id')
                ->nullable()
                ->after('project_subspace_id')
                ->constrained('activity_mappings')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dpr_work_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('activity_mapping_id');
            $table->dropConstrainedForeignId('project_subspace_id');
            $table->dropConstrainedForeignId('project_room_id');
            $table->dropConstrainedForeignId('project_unit_id');
            $table->dropConstrainedForeignId('project_floor_id');
            $table->dropConstrainedForeignId('project_block_id');
        });
    }
};