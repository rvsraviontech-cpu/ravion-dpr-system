<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['material_received_items', 'material_consumed_items'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'material_variant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('material_variant_id')->nullable()->index();
                });
            }
        }
    }
    public function down(): void
    {
        foreach (['material_received_items', 'material_consumed_items'] as $tableName) {
            if (Schema::hasColumn($tableName, 'material_variant_id')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn('material_variant_id'));
            }
        }
    }
};
