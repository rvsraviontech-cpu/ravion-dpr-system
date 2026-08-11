<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_received_photos', function (Blueprint $table) {
            $table->foreignId('material_received_item_id')
                ->nullable()
                ->after('material_received_id')
                ->constrained('material_received_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('material_received_photos', function (Blueprint $table) {
            $table->dropConstrainedForeignId(
                'material_received_item_id'
            );
        });
    }
};