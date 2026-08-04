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
        Schema::create(
            'weekly_wage_sheet_details',
            function (Blueprint $table): void {
                $table->id();

                /*
                |--------------------------------------------------------------------------
                | Parent Wage Sheet
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId('weekly_wage_sheet_id')
                    ->constrained('weekly_wage_sheets')
                    ->cascadeOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Labour Reference
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId('labour_id')
                    ->constrained('labours')
                    ->restrictOnDelete();

                $table
                    ->foreignId('designation_role_id')
                    ->nullable()
                    ->constrained('designation_roles')
                    ->nullOnDelete();

                $table
                    ->foreignId('labour_category_id')
                    ->nullable()
                    ->constrained('labour_categories')
                    ->nullOnDelete();

                $table
                    ->foreignId('contractor_id')
                    ->nullable()
                    ->constrained('contractors')
                    ->nullOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Attendance Summary
                |--------------------------------------------------------------------------
                */

                $table
                    ->decimal('full_days', 8, 2)
                    ->default(0);

                $table
                    ->decimal('half_days', 8, 2)
                    ->default(0);

                $table
                    ->decimal('payable_days', 8, 2)
                    ->default(0);

                $table
                    ->decimal('absent_days', 8, 2)
                    ->default(0);

                $table
                    ->decimal('leave_days', 8, 2)
                    ->default(0);

                $table
                    ->decimal('weekly_off_days', 8, 2)
                    ->default(0);

                $table
                    ->decimal('holiday_days', 8, 2)
                    ->default(0);

                $table
                    ->decimal('normal_hours', 10, 2)
                    ->default(0);

                $table
                    ->decimal('ot_hours', 10, 2)
                    ->default(0);

                /*
                |--------------------------------------------------------------------------
                | Wage Rate Snapshot
                |--------------------------------------------------------------------------
                |
                | These values are copied from Labour Master when the weekly sheet
                | is generated. Later changes to Labour Master must not change
                | historical wage sheets.
                |
                */

                $table
                    ->string('wage_basis', 30)
                    ->default('daily');

                $table
                    ->decimal('daily_wage_rate', 12, 2)
                    ->default(0);

                $table
                    ->decimal('standard_hours_per_day', 6, 2)
                    ->default(8);

                $table
                    ->decimal('ot_hourly_rate', 12, 4)
                    ->default(0);

                /*
                |--------------------------------------------------------------------------
                | Calculated Wage Values
                |--------------------------------------------------------------------------
                */

                $table
                    ->decimal('normal_wage', 15, 2)
                    ->default(0);

                $table
                    ->decimal('ot_wage', 15, 2)
                    ->default(0);

                $table
                    ->decimal('gross_wage', 15, 2)
                    ->default(0);

                /*
                |--------------------------------------------------------------------------
                | Labour-Level Adjustments
                |--------------------------------------------------------------------------
                */

                $table
                    ->decimal('additions', 15, 2)
                    ->default(0);

                $table
                    ->decimal('deductions', 15, 2)
                    ->default(0);

                $table
                    ->decimal('net_payable', 15, 2)
                    ->default(0);

                /*
                |--------------------------------------------------------------------------
                | Optional Manual Adjustment Information
                |--------------------------------------------------------------------------
                */

                $table
                    ->text('adjustment_reason')
                    ->nullable();

                $table
                    ->text('remarks')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Record Control
                |--------------------------------------------------------------------------
                */

                $table
                    ->boolean('is_active')
                    ->default(true)
                    ->index();

                $table->timestamps();
                $table->softDeletes();

                /*
                |--------------------------------------------------------------------------
                | Constraints and Indexes
                |--------------------------------------------------------------------------
                */

                $table->unique(
                    [
                        'weekly_wage_sheet_id',
                        'labour_id',
                    ],
                    'weekly_wage_sheet_labour_unique'
                );

                $table->index([
                    'weekly_wage_sheet_id',
                    'is_active',
                ]);

                $table->index([
                    'labour_id',
                    'is_active',
                ]);
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'weekly_wage_sheet_details'
        );
    }
};