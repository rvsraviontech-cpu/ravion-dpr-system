<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'material_dispatch_item_allocations',
            function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('material_dispatch_item_id');
                $table->unsignedBigInteger('material_requirement_item_id')->nullable();

                $table->decimal('allocated_quantity', 14, 3);
                $table->decimal('received_quantity', 14, 3)->default(0);

                $table->timestamps();

                $table->foreign(
                    'material_dispatch_item_id',
                    'mdi_alloc_dispatch_item_fk'
                )
                    ->references('id')
                    ->on('material_dispatch_items')
                    ->cascadeOnDelete();

                $table->foreign(
                    'material_requirement_item_id',
                    'mdi_alloc_requirement_item_fk'
                )
                    ->references('id')
                    ->on('material_requirement_items')
                    ->nullOnDelete();

                $table->unique(
                    [
                        'material_dispatch_item_id',
                        'material_requirement_item_id',
                    ],
                    'mdi_alloc_dispatch_requirement_unique'
                );

                $table->index(
                    'material_requirement_item_id',
                    'mdi_alloc_requirement_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('material_dispatch_item_allocations');
    }
};