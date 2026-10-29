<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_done_bible_template_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_type_id')->constrained('work_done_bible_project_types')->restrictOnDelete();
            $table->foreignId('operation_id')->constrained('work_done_bible_operations')->restrictOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->string('review_status', 20)->default('Pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
            $table->unique(['project_type_id', 'operation_id'], 'wd_bible_template_operation_unique');
            $table->index(['project_type_id', 'review_status', 'is_enabled'], 'wd_bible_template_approved_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_done_bible_template_operations');
    }
};
