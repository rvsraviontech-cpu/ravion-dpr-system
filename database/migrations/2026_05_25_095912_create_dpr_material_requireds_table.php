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
        Schema::create('dpr_material_requireds', function (Blueprint $table) {

    $table->id();

    $table->foreignId('dpr_id')
        ->constrained()
        ->onDelete('cascade');

    $table->foreignId('material_id')
        ->constrained()
        ->onDelete('cascade');

    $table->decimal(
        'required_quantity',
        12,
        2
    )->default(0);

    $table->date('required_date')
        ->nullable();

    $table->enum(
        'priority',
        [
            'Normal',
            'Urgent',
            'Critical'
        ]
    )->default('Normal');

    $table->text('reason')
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
        Schema::dropIfExists('dpr_material_requireds');
    }
};
