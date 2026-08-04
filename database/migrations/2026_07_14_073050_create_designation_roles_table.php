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
        Schema::create('designation_roles', function (Blueprint $table) {
            $table->id();

            $table->string('code', 30)->unique();
            $table->string('name', 150);

            /*
             * Optional relationship to the existing Labour Type Master.
             *
             * Labour Type represents the Trade / Manpower Category.
             *
             * Examples:
             * Masonry      -> Mason, Head Mason, Mason Helper
             * Electrical   -> Electrician, Electrical Helper
             * Plumbing     -> Plumber, Plumbing Helper
             *
             * Nullable allows a designation to remain generic
             * and reusable across multiple labour types.
             */
            $table->foreignId('labour_type_id')
                ->nullable()
                ->constrained('labour_types')
                ->nullOnDelete();

            /*
             * Optional relationship to Skill Category.
             *
             * Examples:
             * Mason Helper -> Unskilled / Semi-Skilled
             * Mason        -> Skilled
             * Foreman      -> Highly Skilled
             */
            $table->foreignId('skill_category_id')
                ->nullable()
                ->constrained('skill_categories')
                ->nullOnDelete();

            /*
             * System records are seeded and protected.
             * Custom records are created by authorized users.
             */
            $table->boolean('is_system')->default(false);

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_active')->default(true);

            $table->text('remarks')->nullable();

            $table->timestamps();

            /*
             * Prevent duplicate designation names
             * inside the same Labour Type.
             */
            $table->unique(
                ['labour_type_id', 'name'],
                'designation_roles_labour_type_name_unique'
            );

            $table->index('labour_type_id');
            $table->index('skill_category_id');
            $table->index('sort_order');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('designation_roles');
    }
};