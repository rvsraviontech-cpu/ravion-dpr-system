<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machinery_equipment_allocations', function (Blueprint $table) {
            $table->id();

            /*
             * Equipment being moved / allocated.
             */
            $table->foreignId('machinery_equipment_id')
                ->constrained('machinery_equipment')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Transaction identity.
             */
            $table->string('allocation_number', 50)->unique();

            /*
             * Allocation / movement type.
             *
             * initial_allocation
             * transfer
             * return
             * temporary_transfer
             * quantity_adjustment
             */
            $table->string('movement_type', 30);

            /*
             * Source and destination.
             *
             * NULL project means Company Yard / Unallocated.
             */
            $table->foreignId('from_project_id')
                ->nullable()
                ->constrained('projects')
                ->nullOnDelete();

            $table->foreignId('to_project_id')
                ->nullable()
                ->constrained('projects')
                ->nullOnDelete();

            /*
             * Quantity moved.
             *
             * Individual equipment will always move as 1.
             * Pooled equipment may move partially.
             */
            $table->decimal('quantity', 12, 3)->default(1);
            $table->string('unit', 30)->default('Nos');

            /*
             * Movement timing.
             */
            $table->date('movement_date');
            $table->time('movement_time')->nullable();

            /*
             * Useful for temporary allocations / rentals / loaned equipment.
             */
            $table->date('expected_return_date')->nullable();
            $table->date('actual_return_date')->nullable();

            /*
             * Transfer / movement documentation.
             */
            $table->string('reference_number', 100)->nullable();
            $table->string('challan_number', 100)->nullable();

            /*
             * Transport details.
             */
            $table->string('vehicle_number', 50)->nullable();
            $table->string('driver_name', 150)->nullable();
            $table->string('driver_mobile', 30)->nullable();

            /*
             * Workflow.
             *
             * pending
             * in_transit
             * received
             * cancelled
             */
            $table->string('status', 30)->default('received');

            /*
             * User responsible for initiating movement.
             */
            $table->foreignId('transferred_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('transferred_at')->nullable();

            /*
             * Destination acknowledgement.
             */
            $table->foreignId('received_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('received_at')->nullable();

            /*
             * Cancellation audit.
             */
            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            /*
             * General notes.
             */
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

            /*
             * Reporting / lookup indexes.
             */
            $table->index(
                ['machinery_equipment_id', 'movement_date'],
                'mea_equipment_date_idx'
            );

            $table->index(
                ['from_project_id', 'movement_date'],
                'mea_from_project_date_idx'
            );

            $table->index(
                ['to_project_id', 'movement_date'],
                'mea_to_project_date_idx'
            );

            $table->index(
                ['status', 'movement_date'],
                'mea_status_date_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machinery_equipment_allocations');
    }
};