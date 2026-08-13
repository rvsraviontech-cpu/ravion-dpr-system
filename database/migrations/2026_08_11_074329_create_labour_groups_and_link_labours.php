<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labour_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150)->unique();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::table('labours', function (Blueprint $table): void {
            $table->foreignId('labour_group_id')
                ->nullable()
                ->after('manpower_source_id')
                ->constrained('labour_groups')
                ->nullOnDelete()
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('labours', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('labour_group_id');
        });

        Schema::dropIfExists('labour_groups');
    }
};
