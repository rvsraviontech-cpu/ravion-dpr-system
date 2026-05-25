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
        Schema::create('tomorrow_plans', function (Blueprint $table) {

    $table->id();

    $table->foreignId('dpr_id')
        ->constrained()
        ->onDelete('cascade');

    $table->foreignId('activity_id')
        ->nullable()
        ->constrained()
        ->nullOnDelete();

    $table->decimal(
        'planned_quantity',
        12,
        2
    )->default(0);

    $table->string('unit')
        ->nullable();

    $table->integer('planned_labour')
        ->nullable();

    $table->text('materials_required')
        ->nullable();

    $table->text('machinery_required')
        ->nullable();

    $table->text('risks_constraints')
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
        Schema::dropIfExists('tomorrow_plans');
    }
};
