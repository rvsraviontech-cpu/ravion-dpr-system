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
        Schema::create('dpr_material_receiveds', function (Blueprint $table) {

    $table->id();

    $table->foreignId('dpr_id')
        ->constrained()
        ->onDelete('cascade');

    $table->foreignId('material_id')
        ->constrained()
        ->onDelete('cascade');

    $table->foreignId('vendor_id')
        ->nullable()
        ->constrained()
        ->nullOnDelete();

    $table->decimal(
        'quantity_received',
        12,
        2
    )->default(0);

    $table->string('challan_number')
        ->nullable();

    $table->string('bill_number')
        ->nullable();

    $table->text('remarks')
        ->nullable();

    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dpr_material_receiveds');
    }
};
