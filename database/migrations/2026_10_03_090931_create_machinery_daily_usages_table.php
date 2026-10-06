<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machinery_daily_usages', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Core
            |--------------------------------------------------------------------------
            */
            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('machinery_equipment_id')
                ->constrained('machinery_equipment')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('usage_date');

            /*
            |--------------------------------------------------------------------------
            | Quantity
            |--------------------------------------------------------------------------
            |
            | Individual equipment = normally 1.
            | Pooled equipment may report part/all of the quantity located
            | at the selected project.
            |
            */
            $table->decimal('quantity_used', 12, 3)->default(1);
            $table->string('unit', 50)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Shift / Session
            |--------------------------------------------------------------------------
            */
            $table->string('shift', 30)->default('general');

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Elapsed presence/session duration.
            // This is NOT automatically treated as operating hours.
            $table->decimal('site_hours', 8, 2)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Meter
            |--------------------------------------------------------------------------
            */
            $table->string('meter_type', 30)->default('none');

            $table->decimal('opening_meter_reading', 14, 3)->nullable();
            $table->decimal('closing_meter_reading', 14, 3)->nullable();

            /*
             * For hour-meter equipment this may be derived from
             * closing - opening.
             *
             * Odometer/distance equipment must NOT treat the delta as hours.
             */
            $table->decimal('meter_operating_hours', 8, 2)->nullable();

            /*
             * Used for non-metered equipment or an authorized correction.
             */
            $table->decimal('manual_operating_hours', 8, 2)->nullable();

            /*
             * Final approved/usable operating-hours value for this session.
             */
            $table->decimal('final_operating_hours', 8, 2)->nullable();

            $table->text('manual_hours_reason')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Time Classification
            |--------------------------------------------------------------------------
            */
            $table->decimal('idle_hours', 8, 2)->default(0);
            $table->decimal('breakdown_hours', 8, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Operating Condition
            |--------------------------------------------------------------------------
            */
            $table->string('working_condition', 30)->default('working');

            /*
            |--------------------------------------------------------------------------
            | Operator
            |--------------------------------------------------------------------------
            |
            | operator_user_id is for an ERP user when applicable.
            | operator_name remains available for external drivers/operators
            | who are not system users.
            |
            */
            $table->foreignId('operator_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('operator_name', 150)->nullable();
            $table->string('operator_mobile', 30)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Work Done Integration
            |--------------------------------------------------------------------------
            */
            $table->foreignId('work_done_item_id')
                ->nullable()
                ->constrained('work_done_items')
                ->nullOnDelete();

            $table->foreignId('work_activity_id')
                ->nullable()
                ->constrained('work_activities')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Project Structure / Exact Location
            |--------------------------------------------------------------------------
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
            | Fuel / Energy
            |--------------------------------------------------------------------------
            |
            | No rates/costs are stored here.
            | This table is safe for operational Engineer workflows.
            |
            */
            $table->decimal('fuel_energy_quantity', 12, 3)->nullable();
            $table->string('fuel_energy_unit', 30)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Notes
            |--------------------------------------------------------------------------
            */
            $table->text('work_description')->nullable();
            $table->text('remarks')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status / Audit
            |--------------------------------------------------------------------------
            */
            $table->string('status', 30)->default('submitted');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */
            $table->index(
                ['project_id', 'usage_date'],
                'mdu_project_date_idx'
            );

            $table->index(
                ['machinery_equipment_id', 'usage_date'],
                'mdu_equipment_date_idx'
            );

            $table->index(
                ['project_id', 'machinery_equipment_id', 'usage_date'],
                'mdu_project_equipment_date_idx'
            );

            $table->index(
                ['work_done_item_id', 'usage_date'],
                'mdu_work_done_date_idx'
            );

            $table->index(
                ['status', 'usage_date'],
                'mdu_status_date_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machinery_daily_usages');
    }
};