<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('material_requirements', 'dpr_id')) {
            Schema::table('material_requirements', function (Blueprint $table) {
                $table->foreignId('dpr_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('dprs')
                    ->nullOnDelete();

                $table->index(
                    ['dpr_id', 'status'],
                    'material_requirements_dpr_status_index'
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('material_requirements', 'dpr_id')) {
            Schema::table('material_requirements', function (Blueprint $table) {
                $table->dropIndex(
                    'material_requirements_dpr_status_index'
                );

                $table->dropConstrainedForeignId('dpr_id');
            });
        }
    }
};