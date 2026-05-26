<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_subspaces', function (Blueprint $table) {
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

            $table->foreignId('project_room_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name');

            $table->string('type')
                ->nullable();

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
                'project_room_id',
                'name'
            ], 'project_subspaces_unique_location_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_subspaces');
    }
};