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
        Schema::table('material_received_items', function (Blueprint $table) {
            $table->string('purpose_used_for', 255)
                ->nullable()
                ->after('unit_master_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_received_items', function (Blueprint $table) {
            $table->dropColumn('purpose_used_for');
        });
    }
};