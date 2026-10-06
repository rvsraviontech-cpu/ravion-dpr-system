<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_photo_entries', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Core Reporting Information
            |--------------------------------------------------------------------------
            */
            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('photo_date');

            $table->foreignId('reported_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Project Location Hierarchy
            |--------------------------------------------------------------------------
            |
            | Location is intentionally optional.
            | An Engineer must be able to quickly record general site photographs
            | without being forced to select every location level.
            |
            */
            $table->foreignId('project_block_id')
                ->nullable()
                ->constrained('project_blocks')
                ->nullOnDelete();

            $table->foreignId('project_floor_id')
                ->nullable()
                ->constrained('project_floors')
                ->nullOnDelete();

            $table->foreignId('project_unit_id')
                ->nullable()
                ->constrained('project_units')
                ->nullOnDelete();

            $table->foreignId('project_room_id')
                ->nullable()
                ->constrained('project_rooms')
                ->nullOnDelete();

            $table->foreignId('project_subspace_id')
                ->nullable()
                ->constrained('project_subspaces')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Work Execution Classification
            |--------------------------------------------------------------------------
            |
            | These reference the new Work Execution Catalogue.
            | They remain optional because General Site, Safety, Housekeeping,
            | Material and similar photographs may not belong to a Work Activity.
            |
            */
            $table->foreignId('work_package_id')
                ->nullable()
                ->constrained('work_packages')
                ->nullOnDelete();

            $table->foreignId('work_section_id')
                ->nullable()
                ->constrained('work_sections')
                ->nullOnDelete();

            $table->foreignId('work_activity_id')
                ->nullable()
                ->constrained('work_activities')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Optional Contractor
            |--------------------------------------------------------------------------
            */
            $table->foreignId('contractor_id')
                ->nullable()
                ->constrained('contractors')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Site Photo Entry Information
            |--------------------------------------------------------------------------
            */
            $table->string('category', 50)
                ->default('Work Progress');

            $table->string('title', 255)
                ->nullable();

            $table->text('remarks')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Workflow
            |--------------------------------------------------------------------------
            |
            | Site Photos are operational evidence and do not require a separate
            | PMO approval workflow. Draft allows the Engineer to finish an entry
            | before it becomes part of daily reporting.
            |
            */
            $table->string('status', 30)
                ->default('Submitted');

            $table->timestamp('submitted_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Future DPR Integration
            |--------------------------------------------------------------------------
            |
            | DPR aggregation can link an entry without moving/copying its photos.
            | This also prevents the Site Photo record from depending on a DPR
            | existing at the time the Engineer takes the photographs.
            |
            */
            $table->foreignId('dpr_id')
                ->nullable()
                ->constrained('dprs')
                ->nullOnDelete();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Reporting / Filtering Indexes
            |--------------------------------------------------------------------------
            */
            $table->index(
                ['project_id', 'photo_date'],
                'site_photo_entries_project_date_idx'
            );

            $table->index(
                ['reported_by', 'photo_date'],
                'site_photo_entries_reporter_date_idx'
            );

            $table->index(
                ['project_id', 'status'],
                'site_photo_entries_project_status_idx'
            );

            $table->index(
                ['project_id', 'category'],
                'site_photo_entries_project_category_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_photo_entries');
    }
};