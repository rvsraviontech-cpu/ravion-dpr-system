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
        Schema::create('weekly_plans', function (Blueprint $table) {

    $table->id();

    $table->foreignId('project_id')
        ->constrained()
        ->onDelete('cascade');

    $table->foreignId('activity_id')
        ->nullable()
        ->constrained()
        ->nullOnDelete();

    $table->foreignId('user_id')
        ->nullable()
        ->constrained()
        ->nullOnDelete();

    $table->date('week_start_date');

    $table->date('week_end_date');

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

    $table->enum(
        'status',
        [
            'Planned',
            'In Progress',
            'Completed',
            'Delayed'
        ]
    )->default('Planned');

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
        Schema::dropIfExists('weekly_plans');
    }
};
