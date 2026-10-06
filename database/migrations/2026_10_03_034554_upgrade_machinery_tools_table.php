<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('machinery_tools', function (Blueprint $table) {
            /*
             * Existing columns preserved:
             * id
             * machine_name
             * ownership_type   (legacy - ownership moves to equipment register)
             * unit
             * created_at
             * updated_at
             */

            $table->string('code', 50)
                ->nullable()
                ->unique()
                ->after('id');

            $table->string('category', 100)
                ->nullable()
                ->after('machine_name');

            $table->text('description')
                ->nullable()
                ->after('category');

            /*
             * Individual = normally tracked as a specific physical machine.
             * Pooled     = quantity-based tools/equipment may be grouped.
             */
            $table->string('tracking_mode', 30)
                ->default('individual')
                ->after('unit');

            /*
             * none
             * hour_meter
             * odometer
             */
            $table->string('meter_type', 30)
                ->default('none')
                ->after('tracking_mode');

            /*
             * none
             * diesel
             * petrol
             * electric
             * battery
             * hybrid
             * other
             */
            $table->string('fuel_type', 30)
                ->default('none')
                ->after('meter_type');

            $table->boolean('requires_operator')
                ->default(false)
                ->after('fuel_type');

            $table->boolean('requires_meter_reading')
                ->default(false)
                ->after('requires_operator');

            $table->boolean('requires_fuel_tracking')
                ->default(false)
                ->after('requires_meter_reading');

            $table->boolean('is_active')
                ->default(true)
                ->after('requires_fuel_tracking');

            $table->unsignedInteger('sort_order')
                ->default(0)
                ->after('is_active');

            $table->index('category');
            $table->index('is_active');
            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('machinery_tools', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['category', 'is_active']);

            $table->dropColumn([
                'code',
                'category',
                'description',
                'tracking_mode',
                'meter_type',
                'fuel_type',
                'requires_operator',
                'requires_meter_reading',
                'requires_fuel_tracking',
                'is_active',
                'sort_order',
            ]);
        });
    }
};