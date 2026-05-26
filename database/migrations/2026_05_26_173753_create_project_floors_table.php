<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_floors', function (Blueprint $table) {

            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('project_block_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');

            $table->string('name');

            $table->integer('sequence')
                ->default(0);

            $table->boolean('is_active')
                ->default(true);

            $table->text('remarks')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'project_id',
                'project_block_id',
                'name'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_floors');
    }
};