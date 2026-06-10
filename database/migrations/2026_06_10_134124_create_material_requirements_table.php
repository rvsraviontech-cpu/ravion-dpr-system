<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('material_requirements', function (Blueprint $table) {
        $table->id();

        $table->foreignId('project_id')->constrained()->cascadeOnDelete();
        $table->foreignId('project_block_id')->nullable()->constrained('project_blocks')->nullOnDelete();

        $table->foreignId('material_category_id')->constrained('material_categories')->cascadeOnDelete();
        $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();

        $table->decimal('required_quantity', 15, 2);
        $table->string('unit')->nullable();

        $table->date('required_date')->nullable();
        $table->string('priority')->default('Normal');

        $table->string('status')->default('Draft');
        $table->text('remarks')->nullable();

        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('approved_at')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_requirements');
    }
};
