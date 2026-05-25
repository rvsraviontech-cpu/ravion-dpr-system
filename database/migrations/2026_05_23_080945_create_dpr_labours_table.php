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
        Schema::create('dpr_labours', function (Blueprint $table) {

    $table->id();

    $table->foreignId('dpr_id')
        ->constrained()
        ->onDelete('cascade');

    $table->string('labour_type');

    $table->integer('male_count')
        ->default(0);

    $table->integer('female_count')
        ->default(0);

    $table->integer('local_count')
        ->default(0);

    $table->integer('non_local_count')
        ->default(0);

    $table->integer('total_count')
        ->default(0);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dpr_labours');
    }
};
