<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('purchase_order_items', 'material_variant_id')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->foreignId('material_variant_id')
                    ->nullable()
                    ->after('material_type_id')
                    ->constrained('material_variants')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('purchase_order_items', 'material_variant_id')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('material_variant_id');
            });
        }
    }
};
