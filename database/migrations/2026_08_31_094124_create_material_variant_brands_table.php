<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_variant_brands', function (Blueprint $table) {
            $table->id();

            $table->foreignId('material_variant_id');
            $table->foreignId('brand_master_id');

            $table->boolean('is_preferred')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->foreign('material_variant_id', 'mvb_variant_fk')
                ->references('id')
                ->on('material_variants')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('brand_master_id', 'mvb_brand_fk')
                ->references('id')
                ->on('brand_masters')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unique(
                ['material_variant_id', 'brand_master_id'],
                'mvb_variant_brand_uq'
            );

            $table->index(
                ['material_variant_id', 'is_active', 'sort_order'],
                'mvb_variant_active_sort_idx'
            );

            $table->index(
                ['brand_master_id', 'is_active'],
                'mvb_brand_active_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_variant_brands');
    }
};
