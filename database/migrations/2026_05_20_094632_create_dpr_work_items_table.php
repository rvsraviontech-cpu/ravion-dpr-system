<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dpr_work_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dpr_id')->constrained()->onDelete('cascade');

            $table->foreignId('activity_id')->constrained()->onDelete('cascade');

            $table->foreignId('contractor_id')->nullable()->constrained()->onDelete('cascade');

            $table->decimal('quantity_completed', 10, 2)->default(0);

            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dpr_work_items');
    }
};