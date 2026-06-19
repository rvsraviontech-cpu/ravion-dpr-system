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
    Schema::create('labour_report_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('labour_report_id')->constrained()->cascadeOnDelete();
        $table->foreignId('labour_type_id')->nullable()->constrained('labour_types')->nullOnDelete();
        $table->foreignId('contractor_id')->nullable()->constrained('contractors')->nullOnDelete();

        $table->integer('male_count')->default(0);
        $table->integer('female_count')->default(0);
        $table->integer('local_count')->default(0);
        $table->integer('non_local_count')->default(0);
        $table->integer('total_count')->default(0);

        $table->text('remarks')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labour_report_details');
    }
};
