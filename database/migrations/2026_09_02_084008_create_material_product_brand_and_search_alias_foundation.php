<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Product ↔ Brand Mapping
        |--------------------------------------------------------------------------
        |
        | A Brand can belong to many Products.
        | A Product can allow many Brands.
        |
        | Existing brand_masters.material_type_id remains untouched for
        | backward compatibility and historical usage.
        |
        */

        if (! Schema::hasTable('material_product_brand')) {
            Schema::create('material_product_brand', function (Blueprint $table) {
                $table->id();

                $table->foreignId('material_type_id')
                    ->constrained('material_types')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreignId('brand_master_id')
                    ->constrained('brand_masters')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->boolean('is_preferred')
                    ->default(false);

                $table->unsignedInteger('sort_order')
                    ->default(0);

                $table->boolean('is_active')
                    ->default(true);

                $table->text('remarks')
                    ->nullable();

                $table->timestamps();

                $table->unique(
                    ['material_type_id', 'brand_master_id'],
                    'mpb_product_brand_uq'
                );

                $table->index(
                    ['material_type_id', 'is_active', 'sort_order'],
                    'mpb_product_filter_idx'
                );

                $table->index(
                    ['brand_master_id', 'is_active'],
                    'mpb_brand_filter_idx'
                );

                $table->index(
                    ['material_type_id', 'is_preferred'],
                    'mpb_preferred_idx'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Material Search Aliases
        |--------------------------------------------------------------------------
        |
        | Search aliases support fast Engineer-facing product search.
        |
        | Example:
        |
        | Product: Reinforcement Bar
        | Aliases:
        | - TMT
        | - TMT Bar
        | - Rebar
        | - Steel Rod
        |
        | Alias may belong to:
        | - Product only
        | - Product + Variant
        |
        */

        if (! Schema::hasTable('material_search_aliases')) {
            Schema::create('material_search_aliases', function (Blueprint $table) {
                $table->id();

                $table->foreignId('material_type_id')
                    ->constrained('material_types')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreignId('material_variant_id')
                    ->nullable()
                    ->constrained('material_variants')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->string('alias', 255);

                $table->string('normalized_alias', 255)
                    ->nullable();

                $table->unsignedInteger('sort_order')
                    ->default(0);

                $table->boolean('is_active')
                    ->default(true);

                $table->string('source', 100)
                    ->nullable();

                $table->text('remarks')
                    ->nullable();

                $table->timestamps();

                $table->unique(
                    [
                        'material_type_id',
                        'material_variant_id',
                        'normalized_alias',
                    ],
                    'msa_product_variant_alias_uq'
                );

                $table->index(
                    ['material_type_id', 'is_active', 'sort_order'],
                    'msa_product_filter_idx'
                );

                $table->index(
                    ['material_variant_id', 'is_active'],
                    'msa_variant_filter_idx'
                );

                $table->index(
                    ['normalized_alias', 'is_active'],
                    'msa_search_idx'
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_search_aliases');
        Schema::dropIfExists('material_product_brand');
    }
};