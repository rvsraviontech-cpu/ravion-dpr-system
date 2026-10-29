<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_done_bible_template_operation_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_type_id')->constrained('work_done_bible_project_types')->restrictOnDelete();
            $table->foreignId('operation_id')->constrained('work_done_bible_operations')->restrictOnDelete();
            $table->string('old_review_status', 20)->nullable();
            $table->string('new_review_status', 20);
            $table->boolean('old_is_enabled')->nullable();
            $table->boolean('new_is_enabled');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->timestamps();
            $table->index(['project_type_id', 'id'], 'wd_bible_template_audit_history');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('work_done_bible_template_operation_audits');
    }
};
