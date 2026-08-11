<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_done_item_labours', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_done_item_id')
                ->constrained('work_done_items')
                ->cascadeOnDelete();

            $table->foreignId('designation_role_id')
                ->constrained('designation_roles')
                ->restrictOnDelete();

            $table->unsignedInteger('quantity');

            $table->string('remarks', 500)
                ->nullable();

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->timestamps();

            $table->index(
                ['work_done_item_id', 'sort_order'],
                'work_done_item_labours_item_sort_index'
            );

            $table->unique(
                ['work_done_item_id', 'designation_role_id'],
                'work_done_item_labours_item_role_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_done_item_labours');
    }
};
