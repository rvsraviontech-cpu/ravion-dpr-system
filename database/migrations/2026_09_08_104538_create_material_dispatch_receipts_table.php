<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_dispatch_receipts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('material_dispatch_id')
                ->constrained('material_dispatches')
                ->restrictOnDelete();

            $table->string('receipt_number', 50)->unique();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->restrictOnDelete();

            $table->date('receipt_date');

            /*
             * Snapshots
             */
            $table->string('dispatch_number', 50);
            $table->string('project_name', 255);
            $table->string('dispatch_from', 255)->nullable();

            /*
             * Delivery / transport observations at site.
             *
             * These are receipt-side snapshots and need not necessarily
             * match the original dispatch information.
             */
            $table->string('challan_number', 100)->nullable();
            $table->string('vehicle_number', 100)->nullable();
            $table->string('driver_name', 150)->nullable();

            /*
             * Receipt workflow.
             *
             * Draft    = site user is preparing/checking the receipt.
             * Received = receipt has been confirmed.
             * Cancelled = receipt was cancelled before posting.
             */
            $table->string('status', 50)->default('Draft');

            $table->text('receipt_remarks')->nullable();
            $table->text('internal_remarks')->nullable();

            /*
             * Audit
             */
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('received_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('received_at')->nullable();

            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();

            $table->index(
                ['material_dispatch_id', 'receipt_date'],
                'mdr_dispatch_date_idx'
            );

            $table->index(
                ['project_id', 'receipt_date'],
                'mdr_project_date_idx'
            );

            $table->index(
                ['project_id', 'status'],
                'mdr_project_status_idx'
            );

            $table->index(
                ['material_dispatch_id', 'status'],
                'mdr_dispatch_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_dispatch_receipts');
    }
};