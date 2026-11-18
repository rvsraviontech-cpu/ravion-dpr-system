<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_vault_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('slug', 140)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('client_vault_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('client_vault_categories')->restrictOnDelete();
            $table->string('document_number', 100);
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('visibility_role', 40)->default('All');
            $table->string('status', 30)->default('Draft');
            $table->unsignedBigInteger('current_version_id')->nullable();
            $table->unsignedBigInteger('published_version_id')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['project_id', 'document_number'], 'vault_project_document_unique');
            $table->index(['project_id', 'status', 'category_id'], 'vault_client_listing_idx');
        });

        Schema::create('client_vault_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('client_vault_documents')->restrictOnDelete();
            $table->unsignedInteger('revision');
            $table->string('original_filename', 255);
            $table->string('disk', 40)->default('local');
            $table->string('storage_path', 500);
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('file_size');
            $table->char('sha256', 64);
            $table->string('status', 30)->default('Draft');
            $table->text('review_notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['document_id', 'revision'], 'vault_revision_unique');
            $table->index(['document_id', 'status'], 'vault_version_status_idx');
        });

        // Explicit FK constraints prevent pointing at a version of another document only
        // at the application layer; cross-document ownership is checked in controllers.
        Schema::table('client_vault_documents', function (Blueprint $table) {
            $table->foreign('current_version_id', 'vault_current_version_fk')
                ->references('id')->on('client_vault_document_versions')->restrictOnDelete();
            $table->foreign('published_version_id', 'vault_published_version_fk')
                ->references('id')->on('client_vault_document_versions')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('client_vault_documents', function (Blueprint $table) {
            $table->dropForeign('vault_current_version_fk');
            $table->dropForeign('vault_published_version_fk');
        });
        Schema::dropIfExists('client_vault_document_versions');
        Schema::dropIfExists('client_vault_documents');
        Schema::dropIfExists('client_vault_categories');
    }
};
