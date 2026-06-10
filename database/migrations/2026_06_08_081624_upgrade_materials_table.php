<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {

            $table->foreignId('material_category_id')
                ->nullable()
                ->after('id')
                ->constrained('material_categories')
                ->nullOnDelete();

            $table->string('material_code')
                ->nullable()
                ->after('material_category_id');

            $table->string('specification')
                ->nullable()
                ->after('material_name');

            $table->string('brand')
                ->nullable()
                ->after('specification');

            $table->decimal('minimum_stock_level', 12, 2)
                ->default(0)
                ->after('unit');

            $table->boolean('is_active')
                ->default(true)
                ->after('minimum_stock_level');

            $table->text('remarks')
                ->nullable()
                ->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {

            $table->dropForeign(['material_category_id']);

            $table->dropColumn([
                'material_category_id',
                'material_code',
                'specification',
                'brand',
                'minimum_stock_level',
                'is_active',
                'remarks'
            ]);
        });
    }
};