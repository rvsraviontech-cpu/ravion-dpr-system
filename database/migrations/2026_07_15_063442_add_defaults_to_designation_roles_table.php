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
        Schema::table('designation_roles', function (Blueprint $table) {
            /*
            |--------------------------------------------------------------------------
            | Operational Defaults
            |--------------------------------------------------------------------------
            */

            $table->foreignId('default_shift_id')
                ->nullable()
                ->after('skill_category_id')
                ->constrained('shifts')
                ->nullOnDelete();

            $table->decimal('default_normal_shift_hours', 5, 2)
                ->default(8.00)
                ->after('default_shift_id');

            /*
            |--------------------------------------------------------------------------
            | Restricted Financial Defaults
            |--------------------------------------------------------------------------
            |
            | These values are internal defaults only.
            | They must never be exposed to Engineer users.
            |
            */

            $table->string('default_wage_basis', 30)
                ->default('daily')
                ->after('default_normal_shift_hours');

            $table->decimal('default_daily_rate', 12, 2)
                ->nullable()
                ->after('default_wage_basis');

            $table->decimal('default_hourly_rate', 12, 2)
                ->nullable()
                ->after('default_daily_rate');

            $table->decimal('default_monthly_rate', 12, 2)
                ->nullable()
                ->after('default_hourly_rate');

            $table->string('default_ot_calculation_type', 30)
                ->default('fixed_rate')
                ->after('default_monthly_rate');

            $table->decimal('default_ot_rate', 12, 2)
                ->nullable()
                ->after('default_ot_calculation_type');

            $table->decimal('default_ot_multiplier', 6, 2)
                ->nullable()
                ->after('default_ot_rate');

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('default_shift_id');
            $table->index('default_wage_basis');
            $table->index('default_ot_calculation_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('designation_roles', function (Blueprint $table) {
            $table->dropForeign([
                'default_shift_id',
            ]);

            $table->dropIndex([
                'default_shift_id',
            ]);

            $table->dropIndex([
                'default_wage_basis',
            ]);

            $table->dropIndex([
                'default_ot_calculation_type',
            ]);

            $table->dropColumn([
                'default_shift_id',
                'default_normal_shift_hours',
                'default_wage_basis',
                'default_daily_rate',
                'default_hourly_rate',
                'default_monthly_rate',
                'default_ot_calculation_type',
                'default_ot_rate',
                'default_ot_multiplier',
            ]);
        });
    }
};