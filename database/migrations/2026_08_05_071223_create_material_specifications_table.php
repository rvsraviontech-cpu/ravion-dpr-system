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
        Schema::create('material_specifications', function (Blueprint $table) {
            $table->id();

            /*
             * Activity is currently being used as the Material Type.
             *
             * Examples:
             * Cement
             * MS Steel
             * Bricks
             * Sand
             */
            $table->foreignId('activity_id')
                ->constrained('activities')
                ->cascadeOnDelete();

            $table->string('specification_code')
                ->nullable();

            /*
             * Examples:
             * PPC
             * OPC
             * 8 MM
             * 12 MM
             */
            $table->string('specification_name');

            $table->unsignedInteger('sequence')
                ->default(0);

            $table->boolean('is_active')
                ->default(true);

            $table->text('remarks')
                ->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                ['activity_id', 'specification_name'],
                'material_specification_unique'
            );

            $table->index([
                'activity_id',
                'is_active',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_specifications');
    }
};