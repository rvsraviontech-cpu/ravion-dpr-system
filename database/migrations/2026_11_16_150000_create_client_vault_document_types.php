<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_vault_document_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('client_vault_categories')->restrictOnDelete();
            $table->string('title', 255);
            $table->boolean('is_custom')->default(false);
            $table->unsignedInteger('sort_order')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['category_id', 'title']);
        });
        Schema::table('client_vault_documents', function (Blueprint $table) {
            $table->foreignId('document_type_id')->nullable()->after('category_id')
                ->constrained('client_vault_document_types')->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table('client_vault_documents', fn (Blueprint $table) => $table->dropConstrainedForeignId('document_type_id'));
        Schema::dropIfExists('client_vault_document_types');
    }
};
