<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contractor_divisions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contractor_id')
                ->constrained('contractors')
                ->cascadeOnDelete();

            $table->foreignId('activity_division_id')
                ->constrained('activity_divisions')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['contractor_id', 'activity_division_id'],
                'contractor_division_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contractor_divisions');
    }
};