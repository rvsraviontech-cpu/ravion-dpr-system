<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_mappings', function (Blueprint $table) {
            $table->foreignId('activity_division_id')
                ->nullable()
                ->after('activity_id')
                ->constrained('activity_divisions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activity_mappings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('activity_division_id');
        });
    }
};