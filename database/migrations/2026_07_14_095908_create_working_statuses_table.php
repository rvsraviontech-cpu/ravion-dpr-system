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
        Schema::create('working_statuses', function (Blueprint $table) {
            $table->id();

            $table->string('code', 40)->unique();
            $table->string('name', 150);

            /*
             * Used for PMO summaries and exception reporting.
             */
            $table->boolean('counts_as_idle')->default(false);

            /*
             * When true, the attendance form must require remarks
             * or a reason before saving the row.
             */
            $table->boolean('requires_reason')->default(false);

            /*
             * System records are seeded and protected.
             */
            $table->boolean('is_system')->default(false);

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index('counts_as_idle');
            $table->index('requires_reason');
            $table->index('sort_order');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('working_statuses');
    }
};