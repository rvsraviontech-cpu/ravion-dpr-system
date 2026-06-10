<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_receiveds', function (Blueprint $table) {

            $table->foreignId('material_category_id')
                ->nullable()
                ->after('storage_location')
                ->constrained('material_categories')
                ->nullOnDelete();

            $table->foreignId('material_id')
                ->nullable()
                ->after('material_category_id')
                ->constrained('materials')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('material_receiveds', function (Blueprint $table) {

            $table->dropForeign(['material_category_id']);
            $table->dropForeign(['material_id']);

            $table->dropColumn([
                'material_category_id',
                'material_id'
            ]);
        });
    }
};