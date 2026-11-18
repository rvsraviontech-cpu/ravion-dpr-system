<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_quality_test_masters', function (Blueprint $table) {
            $table->id();
            $table->string('code', 24)->unique();
            $table->string('category', 120)->index();
            $table->string('name', 255);
            $table->enum('kind', ['Laboratory', 'Field', 'Inspection'])->default('Laboratory');
            $table->text('description')->nullable();
            $table->string('reference_standard', 255)->nullable();
            $table->text('acceptance_criteria')->nullable();
            $table->string('default_frequency', 255)->nullable();
            $table->boolean('requires_evidence')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('archived_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->index(['category', 'name']);
        });
    }
    public function down(): void { Schema::dropIfExists('client_quality_test_masters'); }
};
