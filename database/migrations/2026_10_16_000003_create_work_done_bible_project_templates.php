<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_done_bible_project_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('project_type', 255);
            $table->text('activation_scope');
            $table->json('source_row');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // This table is intentionally empty after the workbook import.
        // PMO must explicitly approve applicability per project and WD operation.
        Schema::create('work_done_bible_project_applicabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->foreignId('operation_id')->constrained('work_done_bible_operations')->restrictOnDelete();
            $table->enum('applicability', ['Applicable', 'Optional', 'Not Applicable']);
            $table->boolean('is_enabled')->default(true);
            $table->foreignId('configured_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('configured_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'operation_id'], 'wd_bible_project_operation_unique');
            $table->index(['project_id', 'applicability', 'is_enabled'], 'wd_bible_project_applicability_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_done_bible_project_applicabilities');
        Schema::dropIfExists('work_done_bible_project_types');
    }
};
