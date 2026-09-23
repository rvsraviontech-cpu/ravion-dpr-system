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
            $table->foreignId('brand_segment_id')
                ->nullable()
                ->after('id');

            $table->foreign('brand_segment_id', 'brand_masters_segment_fk')
                ->references('id')
                ->on('brand_segments')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brand_masters', function (Blueprint $table) {
            $table->dropForeign('brand_masters_segment_fk');
            $table->dropColumn('brand_segment_id');
        });
    }
};