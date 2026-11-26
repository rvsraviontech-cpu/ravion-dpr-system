<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_drawings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('client_drawing_categories')->restrictOnDelete();
            $table->foreignId('type_id')->constrained('client_drawing_types')->restrictOnDelete();
            $table->string('drawing_number', 100)->unique();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('archived_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['project_id','category_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('client_drawings');
    }
};
