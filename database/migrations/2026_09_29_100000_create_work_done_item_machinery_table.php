<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_done_item_machinery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_done_item_id')->constrained('work_done_items')->cascadeOnDelete();
            $table->string('equipment_name', 200);
            $table->unsignedInteger('quantity');
            $table->decimal('operating_hours', 6, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
            $table->index(['work_done_item_id', 'sort_order'], 'wd_machinery_item_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_done_item_machinery');
    }
};
