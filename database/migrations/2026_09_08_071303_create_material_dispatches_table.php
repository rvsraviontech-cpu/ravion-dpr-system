<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_dispatches', function (Blueprint $table) {
            $table->id();

            $table->string('dispatch_number', 50)->unique();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->restrictOnDelete();

            $table->date('dispatch_date');
            $table->date('expected_delivery_date')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Dispatch Source
            |--------------------------------------------------------------------------
            |
            | For now this is descriptive rather than a warehouse FK because
            | Ravion does not yet operate a formal warehouse/store inventory module.
            |
            */
            $table->string('dispatch_from', 255)
                ->default('Ravion Head Office');

            $table->string('dispatch_from_address', 500)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Destination Snapshot
            |--------------------------------------------------------------------------
            */
            $table->string('project_name', 255);
            $table->text('delivery_address')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Transport / Delivery
            |--------------------------------------------------------------------------
            */
            $table->string('transport_mode', 100)->nullable();
            $table->string('vehicle_number', 100)->nullable();
            $table->string('driver_name', 150)->nullable();
            $table->string('driver_mobile', 30)->nullable();
            $table->string('transporter_name', 255)->nullable();
            $table->string('challan_number', 100)->nullable();
            $table->string('reference_number', 100)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Workflow
            |--------------------------------------------------------------------------
            |
            | Draft
            | Dispatched
            | Partially Received
            | Received
            | Closed
            | Cancelled
            |
            */
            $table->string('status', 50)->default('Draft');

            /*
            |--------------------------------------------------------------------------
            | Remarks
            |--------------------------------------------------------------------------
            */
            $table->text('dispatch_notes')->nullable();
            $table->text('internal_remarks')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Audit / Workflow Users
            |--------------------------------------------------------------------------
            */
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('dispatched_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('dispatched_at')->nullable();

            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();

            $table->index(
                ['project_id', 'dispatch_date'],
                'material_dispatches_project_date_index'
            );

            $table->index(
                ['project_id', 'status'],
                'material_dispatches_project_status_index'
            );

            $table->index(
                ['status', 'dispatch_date'],
                'material_dispatches_status_date_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_dispatches');
    }
};