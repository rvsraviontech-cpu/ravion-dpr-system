<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('client_progress_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('report_number', 50)->unique();
            $table->unsignedInteger('revision')->default(1);
            $table->string('status', 30)->default('Draft');
            $table->string('title', 200);
            $table->text('executive_summary')->nullable();
            $table->text('completed_work')->nullable();
            $table->text('ongoing_work')->nullable();
            $table->text('next_week_outlook')->nullable();
            $table->text('risks_and_actions')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(
    ['project_id', 'period_start', 'period_end'],
    'cpr_project_week_unique'
);
            $table->index(['project_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('client_progress_reports'); }
};
