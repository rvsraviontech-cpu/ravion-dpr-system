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
        Schema::create('material_types', function (Blueprint $table) {

            $table->id();

            /*
             * Existing Activity Division
             * shown as Material Category
             */
            $table->foreignId('activity_division_id')
                ->constrained('activity_divisions')
                ->cascadeOnDelete();

            /*
             * Existing Activity
             * shown as Material
             */
            $table->foreignId('activity_id')
                ->constrained('activities')
                ->cascadeOnDelete();

            /*
             * Generic Material Type
             *
             * Cement
             * Reinforcement Steel
             * Electrical Wire
             * Pipe
             */
            $table->string('material_type_name');

            $table->string('material_type_code')
                ->nullable();

            /*
             * Default Unit
             */
            $table->foreignId('unit_master_id')
                ->nullable()
                ->constrained('unit_masters')
                ->nullOnDelete();

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

            $table->index([
                'activity_division_id',
                'activity_id',
                'is_active'
            ]);

            $table->unique([
                'activity_id',
                'material_type_name'
            ], 'material_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_types');
    }
};