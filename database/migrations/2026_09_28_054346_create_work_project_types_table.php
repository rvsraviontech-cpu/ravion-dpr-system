<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_project_types', function (Blueprint $table) {
            $table->id();

            $table->string('code', 40)->unique();
            $table->string('name', 150);

            $table->text('description')->nullable();

            /*
             * General means that activities tagged with this type
             * are considered broadly applicable across projects.
             */
            $table->boolean('is_general')->default(false);

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(
                ['is_active', 'sort_order'],
                'work_project_types_active_sort_idx'
            );

            $table->index(
                ['is_general', 'is_active'],
                'work_project_types_general_active_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_project_types');
    }
};