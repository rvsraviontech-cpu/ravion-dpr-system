<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_done_bible_project_type_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('bible_project_type_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique('project_id', 'wd_bpta_project_uq');
            $table->foreign('project_id', 'wd_bpta_project_fk')->references('id')->on('projects')->restrictOnDelete();
            $table->foreign('bible_project_type_id', 'wd_bpta_type_fk')->references('id')->on('work_done_bible_project_types')->restrictOnDelete();
            $table->foreign('assigned_by', 'wd_bpta_user_fk')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('work_done_bible_project_type_assignment_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('old_bible_project_type_id')->nullable();
            $table->unsignedBigInteger('new_bible_project_type_id');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamp('changed_at');
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('project_id', 'wd_bptaa_project_fk')->references('id')->on('projects')->restrictOnDelete();
            $table->foreign('old_bible_project_type_id', 'wd_bptaa_old_type_fk')->references('id')->on('work_done_bible_project_types')->restrictOnDelete();
            $table->foreign('new_bible_project_type_id', 'wd_bptaa_new_type_fk')->references('id')->on('work_done_bible_project_types')->restrictOnDelete();
            $table->foreign('changed_by', 'wd_bptaa_user_fk')->references('id')->on('users')->nullOnDelete();
            $table->index(['project_id', 'changed_at'], 'wd_bptaa_history_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_done_bible_project_type_assignment_audits');
        Schema::dropIfExists('work_done_bible_project_type_assignments');
    }
};
