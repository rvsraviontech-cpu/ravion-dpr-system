<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_rooms', function (Blueprint $table) {

            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('project_block_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('project_floor_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('project_unit_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('room_type');

            $table->string('name');

            $table->string('code')
                ->nullable();

            $table->decimal(
                'area_sqft',
                12,
                2
            )->nullable();

            $table->boolean('is_active')
                ->default(true);

            $table->text('remarks')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'project_id',
                'project_block_id',
                'project_floor_id',
                'project_unit_id',
                'name'
            ], 'project_rooms_unique_location_name');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_rooms');
    }
};