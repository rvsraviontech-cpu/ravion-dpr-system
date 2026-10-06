<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machinery_equipment', function (Blueprint $table) {
            $table->id();

            /*
             * Equipment Type Master
             */
            $table->foreignId('machinery_tool_id')
                ->constrained('machinery_tools')
                ->restrictOnDelete();

            /*
             * Identification
             */
            $table->string('equipment_code', 50)->unique();

            $table->string('asset_number', 100)
                ->nullable();

            $table->string('equipment_name', 255)
                ->nullable();

            /*
             * Tracking
             *
             * individual = one identifiable machine
             * pooled     = quantity-based equipment
             */
            $table->string('tracking_mode', 30)
                ->default('individual');

            $table->decimal('quantity', 12, 3)
                ->default(1);

            $table->string('unit', 50)
                ->default('Nos');

            /*
             * Ownership
             *
             * company_owned
             * rented
             * contractor_provided
             */
            $table->string('ownership_type', 30);

            /*
             * Supplier / Contractor
             *
             * vendor_id is primarily used for rented equipment.
             * contractor_id is primarily used for contractor-provided
             * machinery.
             */
            $table->foreignId('vendor_id')
                ->nullable()
                ->constrained('vendors')
                ->nullOnDelete();

            $table->foreignId('contractor_id')
                ->nullable()
                ->constrained('contractors')
                ->nullOnDelete();

            /*
             * Manufacturer / Physical Identification
             */
            $table->string('make', 150)
                ->nullable();

            $table->string('model', 150)
                ->nullable();

            $table->string('serial_number', 150)
                ->nullable();

            $table->string('registration_number', 100)
                ->nullable();

            $table->unsignedSmallInteger('manufacture_year')
                ->nullable();

            /*
             * Capacity
             *
             * Examples:
             * 20 Ton
             * 5 KVA
             * 500 L
             * 3 Cu.m
             */
            $table->decimal('capacity', 14, 3)
                ->nullable();

            $table->string('capacity_unit', 50)
                ->nullable();

            /*
             * Operational Characteristics
             *
             * Copied initially from the Equipment Type Master so the
             * physical equipment can have machine-specific overrides.
             */
            $table->string('meter_type', 30)
                ->default('none');

            $table->string('fuel_type', 30)
                ->default('none');

            $table->decimal('current_meter_reading', 14, 2)
                ->nullable();

            /*
             * Company-owned acquisition information.
             *
             * Commercial values remain backend/admin/accounting data.
             */
            $table->date('purchase_date')
                ->nullable();

            $table->string('purchase_reference', 150)
                ->nullable();

            $table->decimal('purchase_value', 15, 2)
                ->nullable();

            /*
             * Current location is only a convenience snapshot.
             *
             * Proper movement history will be stored separately in
             * machinery_allocations in the next phase.
             */
            $table->foreignId('current_project_id')
                ->nullable()
                ->constrained('projects')
                ->nullOnDelete();

            /*
             * Lifecycle Status
             *
             * available
             * allocated
             * working
             * idle
             * breakdown
             * maintenance
             * returned
             * inactive
             */
            $table->string('status', 30)
                ->default('available');

            $table->date('commissioned_date')
                ->nullable();

            $table->date('decommissioned_date')
                ->nullable();

            $table->text('remarks')
                ->nullable();

            $table->boolean('is_active')
                ->default(true);

            /*
             * Audit ownership
             */
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
             * Indexes
             */
            $table->index('ownership_type');
            $table->index('tracking_mode');
            $table->index('status');
            $table->index('is_active');
            $table->index(['current_project_id', 'status']);
            $table->index(['machinery_tool_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machinery_equipment');
    }
};