<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_common_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('suggested_weightage', 8, 4)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('active');
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
            $table->index(['status', 'sort_order']);
        });
        Schema::create('client_common_stage_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_id')->constrained('client_common_stages')->restrictOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('suggested_weightage', 8, 4)->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('active');
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
            $table->index(['stage_id', 'status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_common_stage_tasks');
        Schema::dropIfExists('client_common_stages');
    }
};
