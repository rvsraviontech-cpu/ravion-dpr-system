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
    Schema::create('material_verifications', function (Blueprint $table) {
        $table->id();

        $table->foreignId('material_received_id')
            ->constrained('material_receiveds')
            ->cascadeOnDelete();

        $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('project_block_id')->nullable()->constrained('project_blocks')->nullOnDelete();
        $table->foreignId('material_category_id')->nullable()->constrained('material_categories')->nullOnDelete();
        $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();

        $table->decimal('received_quantity', 12, 2)->default(0);
        $table->decimal('accepted_quantity', 12, 2)->default(0);
        $table->decimal('short_quantity', 12, 2)->default(0);
        $table->decimal('damaged_quantity', 12, 2)->default(0);
        $table->decimal('rejected_quantity', 12, 2)->default(0);

        $table->string('unit')->nullable();

        $table->string('verification_status')->default('Pending');

        $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('verified_at')->nullable();

        $table->text('verification_remarks')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_verifications');
    }
};
