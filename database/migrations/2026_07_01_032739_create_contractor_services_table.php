<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contractor_services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contractor_id')
                ->constrained('contractors')
                ->cascadeOnDelete();

            $table->foreignId('contractor_service_category_id')
                ->constrained('contractor_service_categories')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'contractor_id',
                'contractor_service_category_id'
            ], 'contractor_service_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contractor_services');
    }
};