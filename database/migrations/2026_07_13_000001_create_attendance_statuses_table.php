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
        Schema::create('attendance_statuses', function (Blueprint $table) {
            $table->id();

            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->string('short_name', 50)->nullable();

            $table->boolean('counts_as_present')->default(false);
            $table->boolean('counts_as_absent')->default(false);

            $table->decimal('payable_factor', 5, 2)->default(0);

            $table->boolean('allows_normal_hours')->default(false);
            $table->boolean('allows_ot_hours')->default(false);
            $table->boolean('requires_working_status')->default(false);

            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
            $table->index('counts_as_present');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_statuses');
    }
};