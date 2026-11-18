<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('mobile', 30)->nullable();
            $table->string('password');
            $table->string('status', 20)->default('Pending');
            $table->boolean('must_change_password')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('client_project_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_user_id')->constrained('client_users')->restrictOnDelete();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->string('role', 40); // Owner/Decision Maker, Technical Viewer, Finance Viewer, Read-only Viewer
            $table->string('status', 20)->default('Pending');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('revocation_reason')->nullable();
            $table->timestamps();
            $table->index(['client_user_id', 'project_id', 'status'], 'client_grants_access_idx');
        });

        Schema::create('client_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->string('source_module', 80);
            $table->unsignedBigInteger('source_record_id');
            $table->string('source_revision', 100);
            $table->string('visibility_role', 80)->default('All');
            $table->string('approval_status', 30)->default('Draft');
            $table->string('withdrawal_status', 30)->default('Active');
            $table->foreignId('superseded_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['project_id', 'approval_status', 'published_at'], 'client_publications_visible_idx');
        });

        Schema::create('client_publication_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_publication_id')->constrained('client_publications')->restrictOnDelete();
            $table->unsignedInteger('version');
            $table->string('source_revision', 100);
            $table->string('status', 30);
            $table->text('reason')->nullable();
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acted_at');
            $table->timestamps();
            $table->unique(['client_publication_id', 'version'], 'client_pub_version_unique');
        });

        Schema::create('client_audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('actor_type', 30);
            $table->unsignedBigInteger('actor_id');
            $table->string('action', 100);
            $table->string('subject_type', 100);
            $table->unsignedBigInteger('subject_id');
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['subject_type', 'subject_id']);
            $table->index(['project_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_audit_events');
        Schema::dropIfExists('client_publication_versions');
        Schema::dropIfExists('client_publications');
        Schema::dropIfExists('client_project_grants');
        Schema::dropIfExists('client_users');
    }
};
