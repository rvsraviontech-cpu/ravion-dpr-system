<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('client_quality_project_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->foreignId('test_master_id')->constrained('client_quality_test_masters')->restrictOnDelete();
            $table->string('location_label', 255)->nullable();
            $table->string('stage_label', 255)->nullable();
            $table->date('planned_date')->nullable();
            $table->string('frequency_override',255)->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable();
            $table->timestamps();
            $table->index(['project_id','test_master_id']);
        });
        Schema::create('client_quality_test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_test_id')->constrained('client_quality_project_tests')->restrictOnDelete();
            $table->unsignedInteger('attempt_no');
            $table->date('tested_on');
            $table->string('result',20); // Passed / Failed / Inconclusive
            $table->text('observations')->nullable();
            $table->text('corrective_action')->nullable();
            $table->string('status',20)->default('Draft'); // Draft / Submitted / Verified / Approved / Returned
            $table->foreignId('created_by')->nullable();
            $table->foreignId('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('published_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('withdrawn_by')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->text('withdrawal_reason')->nullable();
            $table->timestamps();
            $table->unique(['project_test_id','attempt_no'],'quality_result_attempt_unique');
        });
        Schema::create('client_quality_result_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained('client_quality_test_results')->restrictOnDelete();
            $table->string('disk',30)->default('local');
            $table->string('path',500);
            $table->string('original_name',255);
            $table->string('mime_type',100)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->foreignId('uploaded_by')->nullable();
            $table->timestamps();
        });
        Schema::create('client_quality_result_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained('client_quality_test_results')->restrictOnDelete();
            $table->string('action',50);
            $table->foreignId('actor_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void {
        Schema::dropIfExists('client_quality_result_events');
        Schema::dropIfExists('client_quality_result_evidence');
        Schema::dropIfExists('client_quality_test_results');
        Schema::dropIfExists('client_quality_project_tests');
    }
};
