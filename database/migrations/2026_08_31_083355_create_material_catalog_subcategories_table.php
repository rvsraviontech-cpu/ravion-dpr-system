<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_catalog_subcategories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger(
                'material_catalog_category_id'
            );

            $table->string('code', 50);
            $table->string('name', 180);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign(
                'material_catalog_category_id',
                'mcsc_category_fk'
            )
                ->references('id')
                ->on('material_catalog_categories')
                ->cascadeOnDelete();

            $table->unique(
                [
                    'material_catalog_category_id',
                    'code',
                ],
                'mcsc_category_code_uq'
            );

            $table->index(
                [
                    'material_catalog_category_id',
                    'is_active',
                    'sort_order',
                ],
                'mcsc_category_active_sort_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_catalog_subcategories');
    }
};
