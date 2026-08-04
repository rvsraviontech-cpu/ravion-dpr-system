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
        Schema::create('weekly_wage_sheets', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Identification
            |--------------------------------------------------------------------------
            */

            $table
                ->string('wage_sheet_number', 50)
                ->unique();

            $table
                ->foreignId('project_id')
                ->constrained('projects')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Wage Period
            |--------------------------------------------------------------------------
            */

            $table->date('week_start_date');
            $table->date('week_end_date');

            /*
            |--------------------------------------------------------------------------
            | Workflow
            |--------------------------------------------------------------------------
            */

            $table
                ->string('status', 30)
                ->default('draft')
                ->index();

            /*
             * Supported workflow:
             *
             * draft
             * calculated
             * submitted
             * approved
             * rejected
             * paid
             */

            /*
            |--------------------------------------------------------------------------
            | Calculated Totals
            |--------------------------------------------------------------------------
            */

            $table
                ->unsignedInteger('total_labours')
                ->default(0);

            $table
                ->decimal('total_full_days', 10, 2)
                ->default(0);

            $table
                ->decimal('total_half_days', 10, 2)
                ->default(0);

            $table
                ->decimal('total_payable_days', 10, 2)
                ->default(0);

            $table
                ->decimal('total_normal_wages', 15, 2)
                ->default(0);

            $table
                ->decimal('total_ot_hours', 12, 2)
                ->default(0);

            $table
                ->decimal('total_ot_wages', 15, 2)
                ->default(0);

            $table
                ->decimal('total_labour_additions', 15, 2)
                ->default(0);

            $table
                ->decimal('total_labour_deductions', 15, 2)
                ->default(0);

            /*
             * Project/site expenses such as:
             *
             * Auto charge
             * Transport charge
             * Food charge
             * Tool charge
             * Other site-related wage-sheet charges
             */
            $table
                ->decimal('total_site_charges', 15, 2)
                ->default(0);

            $table
                ->decimal('gross_labour_wages', 15, 2)
                ->default(0);

            $table
                ->decimal('net_labour_wages', 15, 2)
                ->default(0);

            $table
                ->decimal('total_project_payable', 15, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Generation and Workflow Users
            |--------------------------------------------------------------------------
            */

            $table
                ->foreignId('generated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table
                ->timestamp('generated_at')
                ->nullable();

            $table
                ->foreignId('submitted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table
                ->timestamp('submitted_at')
                ->nullable();

            $table
                ->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table
                ->timestamp('approved_at')
                ->nullable();

            $table
                ->foreignId('rejected_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table
                ->timestamp('rejected_at')
                ->nullable();

            $table
                ->text('rejection_reason')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Payment Information
            |--------------------------------------------------------------------------
            */

            $table
                ->foreignId('paid_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table
                ->date('payment_date')
                ->nullable();

            $table
                ->string('payment_method', 50)
                ->nullable();

            $table
                ->string('payment_reference', 150)
                ->nullable();

            $table
                ->timestamp('paid_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | General Information
            |--------------------------------------------------------------------------
            */

            $table
                ->text('remarks')
                ->nullable();

            $table
                ->boolean('is_active')
                ->default(true)
                ->index();

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
    [
        'project_id',
        'week_start_date',
        'week_end_date',
    ],
    'wage_sheet_project_week_idx'
);

$table->index(
    [
        'project_id',
        'status',
    ],
    'wage_sheet_project_status_idx'
);

$table->index(
    [
        'week_start_date',
        'week_end_date',
        'status',
    ],
    'wage_sheet_period_status_idx'
);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_wage_sheets');
    }
};