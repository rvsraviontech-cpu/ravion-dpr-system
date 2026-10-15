<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_bible_trades', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('normalized_name', 255)->unique();
            $table->string('code', 80)->nullable()->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('material_bible_main_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_bible_trade_id')
                ->constrained(table: 'material_bible_trades', indexName: 'mb_main_trade_fk')->restrictOnDelete();
            $table->string('name', 255);
            $table->string('normalized_name', 255);
            $table->string('code', 80)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['material_bible_trade_id', 'normalized_name'], 'mb_main_trade_name_unique');
            $table->index(['material_bible_trade_id', 'is_active'], 'mb_main_trade_active_idx');
        });

        Schema::create('material_bible_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_bible_main_category_id')
                ->constrained(table: 'material_bible_main_categories', indexName: 'mb_sub_main_fk')->restrictOnDelete();
            $table->string('name', 255);
            $table->string('normalized_name', 255);
            $table->string('code', 80)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['material_bible_main_category_id', 'normalized_name'], 'mb_sub_main_name_unique');
            $table->index(['material_bible_main_category_id', 'is_active'], 'mb_sub_main_active_idx');
        });

        Schema::create('material_bible_product_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_bible_sub_category_id')
                ->constrained(table: 'material_bible_sub_categories', indexName: 'mb_map_sub_fk')->restrictOnDelete();
            $table->foreignId('material_type_id')
                ->constrained(table: 'material_types', indexName: 'mb_map_product_fk')->restrictOnDelete();
            // Null means the classification applies to the product as a whole.
            $table->foreignId('material_variant_id')->nullable()
                ->constrained(table: 'material_variants', indexName: 'mb_map_variant_fk')->restrictOnDelete();
            // 0 = product-level mapping; otherwise equals material_variant_id.
            // This non-null key prevents duplicate product-level mappings in MySQL.
            $table->unsignedBigInteger('variant_scope_key')->default(0);
            $table->string('source_code', 80)->default('RAVION_BIBLE_20260912');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(
                ['material_bible_sub_category_id', 'material_type_id', 'variant_scope_key'],
                'mb_mapping_sub_product_scope_unique'
            );
            $table->index(['material_type_id', 'is_active'], 'mb_mapping_product_active_idx');
            $table->index(['material_variant_id'], 'mb_mapping_variant_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_bible_product_mappings');
        Schema::dropIfExists('material_bible_sub_categories');
        Schema::dropIfExists('material_bible_main_categories');
        Schema::dropIfExists('material_bible_trades');
    }
};
