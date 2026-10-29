<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_done_bible_project_applicability_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('operation_id');
            $table->string('old_applicability', 32)->nullable();
            $table->string('new_applicability', 32);
            $table->boolean('old_is_enabled')->nullable();
            $table->boolean('new_is_enabled');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamp('changed_at');
            $table->text('reason');
            $table->timestamps();
            $table->foreign('project_id', 'wd_bpa_audit_project_fk')->references('id')->on('projects')->restrictOnDelete();
            $table->foreign('operation_id', 'wd_bpa_audit_operation_fk')->references('id')->on('work_done_bible_operations')->restrictOnDelete();
            $table->foreign('changed_by', 'wd_bpa_audit_user_fk')->references('id')->on('users')->nullOnDelete();
            $table->index(['project_id', 'changed_at'], 'wd_bpa_audit_project_date_idx');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('work_done_bible_project_applicability_audits');
    }
};
