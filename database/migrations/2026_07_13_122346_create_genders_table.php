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
        Schema::create('genders', function (Blueprint $table) {
            $table->id();

            $table->string('code', 20)->unique();
            $table->string('name', 100);

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index('sort_order');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('genders');
    }
};