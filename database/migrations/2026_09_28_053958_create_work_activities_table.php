<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_package_id')
                ->constrained('work_packages')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('work_section_id')
                ->nullable()
                ->constrained('work_sections')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Optional link to the legacy Activity Master.
             *
             * This preserves compatibility with the existing DPR,
             * Activity Mapping and reporting architecture without
             * making the new Work Done catalogue dependent on it.
             */
            $table->foreignId('legacy_activity_id')
                ->nullable()
                ->constrained('activities')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('code', 60)->unique();

            $table->string('name', 200);

            /*
             * Engineer-friendly explanation of what this work means.
             */
            $table->text('description')->nullable();

            /*
             * Default primary measurement for the work.
             *
             * Examples:
             * Sq.ft
             * Sq.m
             * Cft
             * Cum
             * Rft
             * Rm
             * Kg
             * MT
             * Nos
             * Point
             * Job
             * LS
             *
             * Detailed secondary measurements will be handled
             * separately by activity measurement rules.
             */
            $table->string('default_unit', 50)->nullable();

            /*
             * Controls which resource panels are relevant to
             * this activity in Work Done.
             */
            $table->boolean('allow_materials')->default(true);
            $table->boolean('allow_labour')->default(true);
            $table->boolean('allow_equipment')->default(true);
            $table->boolean('allow_photos')->default(true);

            /*
             * Enables expected material calculation only for
             * activities where reliable norms are configured.
             */
            $table->boolean('material_calculation_enabled')
                ->default(false);

            /*
             * Allows an activity to be selectable directly by
             * Engineers in Work Done.
             */
            $table->boolean('is_selectable')->default(true);

            /*
             * System records are part of the Ravion standard
             * Work Activity catalogue.
             */
            $table->boolean('is_system')->default(false);

            $table->boolean('is_active')->default(true);

            $table->unsignedInteger('sort_order')->default(0);

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(
                ['work_package_id', 'is_active', 'sort_order'],
                'work_activities_package_active_sort_idx'
            );

            $table->index(
                ['work_section_id', 'is_active', 'sort_order'],
                'work_activities_section_active_sort_idx'
            );

            $table->index(
                ['is_selectable', 'is_active'],
                'work_activities_selectable_active_idx'
            );

            $table->index('legacy_activity_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_activities');
    }
};