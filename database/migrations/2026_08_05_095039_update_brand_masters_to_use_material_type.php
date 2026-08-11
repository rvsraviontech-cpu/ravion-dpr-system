<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brand_masters', function (Blueprint $table) {

            $table->foreignId('material_type_id')
                ->nullable()
                ->after('material_category_id')
                ->constrained('material_types')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('brand_masters', function (Blueprint $table) {

            $table->dropConstrainedForeignId(
                'material_type_id'
            );

        });
    }
};