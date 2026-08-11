<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_specifications', function (Blueprint $table) {
            $table->foreignId('material_type_id')
                ->nullable()
                ->after('activity_id')
                ->constrained('material_types')
                ->nullOnDelete();

            $table->index(
                ['material_type_id', 'is_active'],
                'material_specifications_type_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('material_specifications', function (Blueprint $table) {
            $table->dropIndex(
                'material_specifications_type_status_index'
            );

            $table->dropConstrainedForeignId(
                'material_type_id'
            );
        });
    }
};