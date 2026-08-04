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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();

            $table->string('code', 30)->unique();
            $table->string('name', 150);

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->decimal('normal_hours', 5, 2)->default(8.00);

            /*
             * True when the shift ends on the next calendar day.
             * Example: 8:00 PM to 5:00 AM.
             */
            $table->boolean('crosses_midnight')->default(false);

            /*
             * System records are seeded and protected.
             */
            $table->boolean('is_system')->default(false);

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index('sort_order');
            $table->index('is_active');
            $table->index('crosses_midnight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};