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
            'weekly_wage_sheet_charges',
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
                | Charge Information
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('charge_type', 100);

                /*
                 * Examples:
                 *
                 * Auto Charge
                 * Transport Charge
                 * Food Charge
                 * Tool Charge
                 * Loading / Unloading
                 * Other Site Charge
                 */

                $table
                    ->string('description', 255)
                    ->nullable();

                $table
                    ->decimal('amount', 15, 2)
                    ->default(0);

                /*
                |--------------------------------------------------------------------------
                | Optional Work Context
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId('activity_id')
                    ->nullable()
                    ->constrained('activities')
                    ->nullOnDelete();

                $table
                    ->foreignId('contractor_id')
                    ->nullable()
                    ->constrained('contractors')
                    ->nullOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Remarks and Record Control
                |--------------------------------------------------------------------------
                */

                $table
                    ->text('remarks')
                    ->nullable();

                $table
                    ->unsignedInteger('sort_order')
                    ->default(0);

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

                $table->index([
                    'weekly_wage_sheet_id',
                    'is_active',
                ]);

                $table->index([
                    'weekly_wage_sheet_id',
                    'sort_order',
                ]);

                $table->index([
                    'charge_type',
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
            'weekly_wage_sheet_charges'
        );
    }
};