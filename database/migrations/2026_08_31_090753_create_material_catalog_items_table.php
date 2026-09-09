<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_catalog_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('material_catalog_category_id');
            $table->unsignedBigInteger('material_catalog_subcategory_id');

            $table->char('source_key', 40)->unique();
            $table->string('source_item_name', 255);
            $table->string('normalized_name', 255);

            $table->string('suggested_base_name', 255)->nullable();
            $table->string('variant_text', 255)->nullable();

            $table->unsignedBigInteger('suggested_unit_master_id')->nullable();
            $table->unsignedBigInteger('matched_material_type_id')->nullable();

            $table->string('match_status', 30)->default('pending');
            $table->decimal('match_confidence', 5, 2)->nullable();
            $table->text('analysis_notes')->nullable();

            $table->unsignedInteger('source_sequence')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign(
                'material_catalog_category_id',
                'mci_category_fk'
            )
                ->references('id')
                ->on('material_catalog_categories')
                ->cascadeOnDelete();

            $table->foreign(
                'material_catalog_subcategory_id',
                'mci_subcategory_fk'
            )
                ->references('id')
                ->on('material_catalog_subcategories')
                ->cascadeOnDelete();

            $table->foreign(
                'suggested_unit_master_id',
                'mci_unit_fk'
            )
                ->references('id')
                ->on('unit_masters')
                ->nullOnDelete();

            $table->foreign(
                'matched_material_type_id',
                'mci_material_type_fk'
            )
                ->references('id')
                ->on('material_types')
                ->nullOnDelete();

            $table->index(
                ['material_catalog_subcategory_id', 'is_active', 'source_sequence'],
                'mci_subcat_active_sort_idx'
            );

            $table->index(
                ['match_status', 'is_active'],
                'mci_status_active_idx'
            );

            $table->index(
                'normalized_name',
                'mci_normalized_name_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_catalog_items');
    }
};
