<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_work_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained('projects')
                ->restrictOnDelete();

            $table->string('stage_code', 50)->nullable();
            $table->string('stage_name');
            $table->text('description')->nullable();

            $table->decimal('weightage', 8, 4)->default(0);
            $table->unsignedInteger('sort_order')->default(0);

            $table->string('status', 30)->default('active');

            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();

            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();

            $table->timestamps();

            $table->index(['project_id', 'status', 'sort_order']);
        });

        Schema::create('client_work_stage_tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stage_id')
                ->constrained('client_work_stages')
                ->restrictOnDelete();

            $table->string('task_code', 50)->nullable();
            $table->string('task_name');
            $table->text('description')->nullable();

            $table->decimal('weightage', 8, 4)->default(1);
            $table->decimal('completion_percentage', 5, 2)->default(0);

            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 30)->default('active');

            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->date('actual_completion_date')->nullable();

            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();

            $table->timestamps();

            $table->index(['stage_id', 'status', 'sort_order']);
        });

        Schema::create('client_work_progress_history', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->restrictOnDelete();

            $table->foreignId('stage_id')->nullable()
                ->constrained('client_work_stages')
                ->restrictOnDelete();

            $table->foreignId('task_id')->nullable()
                ->constrained('client_work_stage_tasks')
                ->restrictOnDelete();

            $table->string('action', 60);

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->text('remarks')->nullable();

            $table->foreignId('changed_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['project_id', 'created_at']);
        });

        Schema::create('client_work_progress_snapshots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->restrictOnDelete();

            $table->unsignedInteger('revision')->default(1);

            $table->decimal('overall_completion_percentage', 5, 2)
                ->default(0);

            $table->json('stage_snapshot');

            $table->string('status', 40)->default('draft');

            $table->foreignId('created_by')->nullable();
            $table->foreignId('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->foreignId('published_by')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->unique(['project_id', 'revision']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_work_progress_snapshots');
        Schema::dropIfExists('client_work_progress_history');
        Schema::dropIfExists('client_work_stage_tasks');
        Schema::dropIfExists('client_work_stages');
    }
};