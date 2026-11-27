<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 100)->unique();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('client_support_categories')->restrictOnDelete();
            $table->foreignId('client_user_id')->constrained('client_users')->restrictOnDelete();
            $table->foreignId('grant_id')->constrained('client_project_grants')->restrictOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('subject', 255);
            $table->string('status', 30)->default('Open');
            $table->string('priority', 20)->default('Normal');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->index(['project_id','status'], 'cs_tickets_project_status_idx');
            $table->index(['client_user_id','project_id'], 'cs_tickets_client_project_idx');
            $table->index(['assigned_to','status'], 'cs_tickets_assignee_status_idx');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_support_tickets');
    }
};
