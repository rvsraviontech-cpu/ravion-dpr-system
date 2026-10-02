<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_sections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_package_id')
                ->constrained('work_packages')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('code', 40)->unique();
            $table->string('name', 150);

            $table->text('description')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(
                ['work_package_id', 'is_active', 'sort_order'],
                'work_sections_package_active_sort_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_sections');
    }
};