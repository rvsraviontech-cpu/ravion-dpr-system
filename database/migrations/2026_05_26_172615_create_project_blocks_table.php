<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_blocks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('name');

            $table->string('code')
                ->nullable();

            $table->enum('type', [
                'Block',
                'Building',
                'Tower',
                'Villa',
                'External Area',
                'Not Applicable',
            ])->default('Building');

            $table->boolean('is_active')
                ->default(true);

            $table->text('remarks')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'project_id',
                'name',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_blocks');
    }
};