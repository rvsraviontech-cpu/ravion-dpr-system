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
        Schema::create('machinery_tools', function (Blueprint $table) {

    $table->id();

    $table->string('machine_name');

    $table->enum(
        'ownership_type',
        [
            'Owned',
            'Rented',
            'Contractor Provided'
        ]
    )->default('Owned');

    $table->string('unit')
        ->default('Nos');

    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machinery_tools');
    }
};
