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
        Schema::table('dpr_labours', function (Blueprint $table) {

    $table->foreignId('labour_type_id')
        ->nullable()
        ->constrained('labour_types')
        ->onDelete('cascade');

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dpr_labours', function (Blueprint $table) {

    $table->dropForeign(['labour_type_id']);

    $table->dropColumn('labour_type_id');

});
    }
};
