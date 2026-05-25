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
        Schema::create('dpr_machinery_tools', function (Blueprint $table) {

    $table->id();

    $table->foreignId('dpr_id')
        ->constrained()
        ->onDelete('cascade');

    $table->foreignId('machinery_tool_id')
        ->constrained()
        ->onDelete('cascade');

    $table->integer('quantity')
        ->default(1);

    $table->decimal(
        'usage_hours',
        8,
        2
    )->default(0);

    $table->enum(
        'working_condition',
        [
            'Working',
            'Breakdown',
            'Maintenance'
        ]
    )->default('Working');

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
        Schema::dropIfExists('dpr_machinery_tools');
    }
};
