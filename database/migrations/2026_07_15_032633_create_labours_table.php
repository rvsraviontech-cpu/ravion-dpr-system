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
        Schema::create('labours', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Labour Identification
            |--------------------------------------------------------------------------
            */

            $table->string('labour_code', 30)->unique();

            $table->string('full_name', 150);

            $table->string('mobile', 20)->nullable();
            $table->string('alternate_mobile', 20)->nullable();

            $table->foreignId('gender_id')
                ->nullable()
                ->constrained('genders')
                ->nullOnDelete();

            $table->date('date_of_birth')->nullable();

            $table->string('photo_path')->nullable();

            $table->string('identity_type', 50)->nullable();
            $table->string('identity_number', 100)->nullable();

            $table->text('address')->nullable();

            $table->string('emergency_contact_name', 150)->nullable();
            $table->string('emergency_contact_mobile', 20)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Labour Classification
            |--------------------------------------------------------------------------
            */

            $table->foreignId('manpower_source_id')
                ->constrained('manpower_sources')
                ->restrictOnDelete();

            /*
             * Existing broad Labour Category:
             * Structural & Civil, Finishing & Interior, MEP, etc.
             */
            $table->foreignId('labour_category_id')
                ->nullable()
                ->constrained('labour_categories')
                ->nullOnDelete();

            /*
             * Existing Labour Type represents Trade / Manpower Category.
             */
            $table->foreignId('labour_type_id')
                ->nullable()
                ->constrained('labour_types')
                ->nullOnDelete();

            $table->foreignId('skill_category_id')
                ->nullable()
                ->constrained('skill_categories')
                ->nullOnDelete();

            $table->foreignId('designation_role_id')
                ->nullable()
                ->constrained('designation_roles')
                ->nullOnDelete();

            $table->foreignId('default_shift_id')
                ->nullable()
                ->constrained('shifts')
                ->nullOnDelete();

            /*
             * Required later when the selected Manpower Source has
             * requires_contractor = true.
             */
            $table->foreignId('contractor_id')
                ->nullable()
                ->constrained('contractors')
                ->nullOnDelete();

            /*
             * Optional current/default project assignment.
             *
             * Attendance will still store its own project reference,
             * so historical records will not depend on this field.
             */
            $table->foreignId('current_project_id')
                ->nullable()
                ->constrained('projects')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Employment Information
            |--------------------------------------------------------------------------
            */

            $table->date('joining_date')->nullable();
            $table->date('exit_date')->nullable();

            /*
             * Suggested values:
             * active, inactive, on_leave, exited, suspended
             */
            $table->string('employment_status', 30)
                ->default('active');

            /*
             * Suggested values:
             * local, non_local, not_specified
             */
            $table->string('residency_status', 30)
                ->default('not_specified');

            /*
            |--------------------------------------------------------------------------
            | Restricted Financial Information
            |--------------------------------------------------------------------------
            |
            | These fields must never be exposed to Site Engineer users.
            | Financial access will be controlled separately through
            | labour_masters.financial_view and financial_manage permissions.
            |
            */

            /*
             * Suggested values:
             * daily, hourly, monthly, contractor_managed
             */
            $table->string('wage_basis', 30)
                ->default('daily');

            $table->decimal('current_daily_rate', 12, 2)
                ->nullable();

            $table->decimal('current_hourly_rate', 12, 2)
                ->nullable();

            $table->decimal('current_monthly_rate', 12, 2)
                ->nullable();

            /*
             * Suggested values:
             * fixed_rate, multiplier, not_applicable
             */
            $table->string('ot_calculation_type', 30)
                ->default('fixed_rate');

            $table->decimal('current_ot_rate', 12, 2)
                ->nullable();

            $table->decimal('ot_multiplier', 6, 2)
                ->nullable();

            $table->decimal('normal_shift_hours', 5, 2)
                ->default(8.00);

            /*
            |--------------------------------------------------------------------------
            | Record Control
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')->default(true);

            $table->text('remarks')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('full_name');
            $table->index('mobile');
            $table->index('identity_number');

            $table->index('manpower_source_id');
            $table->index('labour_category_id');
            $table->index('labour_type_id');
            $table->index('skill_category_id');
            $table->index('designation_role_id');
            $table->index('default_shift_id');
            $table->index('contractor_id');
            $table->index('current_project_id');

            $table->index('employment_status');
            $table->index('residency_status');
            $table->index('wage_basis');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labours');
    }
};