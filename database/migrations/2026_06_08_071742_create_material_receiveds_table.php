<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_receiveds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dpr_id')
                ->nullable()
                ->constrained('dprs')
                ->nullOnDelete();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

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

            $table->string('storage_location')->nullable();

            $table->string('material_category')->nullable();
            $table->string('material_name');
            $table->string('specification')->nullable();
            $table->string('brand')->nullable();

            $table->decimal('quantity_received', 12, 2)->default(0);
            $table->string('unit')->nullable();

            $table->string('vendor_name')->nullable();

            $table->boolean('supplied_by_contractor')->default(false);

            $table->foreignId('contractor_id')
                ->nullable()
                ->constrained('contractors')
                ->nullOnDelete();

            $table->string('vehicle_number')->nullable();
            $table->string('driver_name')->nullable();

            $table->string('challan_number')->nullable();
            $table->string('bill_number')->nullable();

            $table->date('received_date');
            $table->time('received_time')->nullable();

            $table->string('material_condition')->default('Pending verification');

            $table->decimal('accepted_quantity', 12, 2)->default(0);
            $table->decimal('short_quantity', 12, 2)->default(0);
            $table->decimal('damaged_quantity', 12, 2)->default(0);
            $table->decimal('rejected_quantity', 12, 2)->default(0);

            $table->string('site_engineer_verification_status')->default('Pending');
            $table->string('pmo_verification_status')->default('Pending');
            $table->string('accountant_verification_status')->default('Pending');

            $table->string('status')->default('Draft');

            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_receiveds');
    }
};