<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_support_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->foreignId('role_id')->constrained('client_support_contact_roles')->restrictOnDelete();
            $table->string('name', 150);
            $table->string('phone', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('availability')->nullable();
            $table->boolean('show_phone')->default(false);
            $table->boolean('show_email')->default(false);
            $table->boolean('is_client_visible')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->index(['project_id','is_active','is_client_visible'], 'cs_contacts_visibility_idx');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_support_contacts');
    }
};
