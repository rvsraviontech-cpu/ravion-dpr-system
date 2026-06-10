<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_issues', function (Blueprint $table) {

            $table->foreignId('project_id')
                ->nullable()
                ->after('dpr_id')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('project_block_id')
                ->nullable()
                ->after('project_id')
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

            $table->foreignId('activity_id')
                ->nullable()
                ->after('project_subspace_id')
                ->constrained('activities')
                ->nullOnDelete();

            $table->date('issue_date')
                ->nullable()
                ->after('activity_id');

            $table->string('title')
                ->nullable()
                ->after('issue_type');

            $table->string('root_cause')
                ->nullable()
                ->after('description');

            $table->date('target_closure_date')
                ->nullable()
                ->after('responsible_person');

            $table->date('actual_closure_date')
                ->nullable()
                ->after('target_closure_date');

            $table->boolean('escalated_to_pmo')
                ->default(false)
                ->after('status');

            $table->boolean('escalated_to_management')
                ->default(false)
                ->after('escalated_to_pmo');

            $table->text('resolution')
                ->nullable()
                ->after('escalated_to_management');

            $table->foreignId('created_by')
                ->nullable()
                ->after('resolution')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('site_issues', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
            $table->dropConstrainedForeignId('project_block_id');
            $table->dropConstrainedForeignId('project_floor_id');
            $table->dropConstrainedForeignId('project_unit_id');
            $table->dropConstrainedForeignId('project_room_id');
            $table->dropConstrainedForeignId('project_subspace_id');
            $table->dropConstrainedForeignId('activity_id');
            $table->dropConstrainedForeignId('created_by');

            $table->dropColumn([
                'issue_date',
                'title',
                'root_cause',
                'target_closure_date',
                'actual_closure_date',
                'escalated_to_pmo',
                'escalated_to_management',
                'resolution',
            ]);
        });
    }
};