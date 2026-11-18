<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->string('reference', 40)->unique();
            $table->string('title', 255);
            $table->string('category', 60)->default('Other');
            $table->longText('description');
            $table->string('status', 32)->default('Draft')->index();
            $table->date('response_due_at')->nullable();
            $table->boolean('has_cost_impact')->default(false);
            $table->boolean('has_time_impact')->default(false);
            $table->text('impact_details')->nullable();
            $table->unsignedInteger('revision')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'status']);
        });
        Schema::create('client_decision_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decision_id')->constrained('client_decisions')->cascadeOnDelete();
            $table->string('label', 200);
            $table->text('details')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('client_decision_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decision_id')->constrained('client_decisions')->cascadeOnDelete();
            $table->string('disk', 32)->default('local');
            $table->string('path', 500);
            $table->string('original_name', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('client_decision_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decision_id')->constrained('client_decisions')->restrictOnDelete();
            $table->foreignId('client_user_id')->constrained('client_users')->restrictOnDelete();
            $table->foreignId('option_id')->nullable()->constrained('client_decision_options')->restrictOnDelete();
            $table->string('response', 32);
            $table->text('comments')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('responded_at');
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
            $table->index(['decision_id', 'responded_at']);
        });
        Schema::create('client_decision_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decision_id')->constrained('client_decisions')->restrictOnDelete();
            $table->string('actor_guard', 20);
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action', 60);
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['decision_id', 'created_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('client_decision_events');
        Schema::dropIfExists('client_decision_responses');
        Schema::dropIfExists('client_decision_files');
        Schema::dropIfExists('client_decision_options');
        Schema::dropIfExists('client_decisions');
    }
};
