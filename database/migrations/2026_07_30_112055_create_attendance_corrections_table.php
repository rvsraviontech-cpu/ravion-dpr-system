<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->string('correction_number', 40)->unique();
            $table->foreignId('labour_attendance_id')->constrained('labour_attendances')->restrictOnDelete();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->date('attendance_date');
            $table->text('correction_reason');
            $table->string('status', 30)->default('draft')->index();

            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('applied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('applied_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'attendance_date'], 'acr_project_attendance_date_index');
            $table->index(['labour_attendance_id', 'status'], 'acr_attendance_status_index');
            $table->index(['submitted_by', 'status'], 'acr_submitter_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_corrections');
    }
};