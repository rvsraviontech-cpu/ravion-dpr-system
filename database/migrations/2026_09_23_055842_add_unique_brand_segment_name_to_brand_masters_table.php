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
        Schema::table('brand_masters', function (Blueprint $table) {
            $table->unique(
                ['brand_segment_id', 'brand_name'],
                'brand_masters_segment_name_uq'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brand_masters', function (Blueprint $table) {
            $table->dropUnique(
                'brand_masters_segment_name_uq'
            );
        });
    }
};