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
        Schema::create('site_issues', function (Blueprint $table) {

    $table->id();

    $table->foreignId('dpr_id')
        ->constrained()
        ->onDelete('cascade');

    $table->string('issue_type');

    $table->string('related_activity')
        ->nullable();

    $table->text('description');

    $table->string('responsible_person')
        ->nullable();

    $table->enum(
        'priority',
        [
            'Low',
            'Medium',
            'High',
            'Critical'
        ]
    )->default('Medium');

    $table->enum(
        'status',
        [
            'Open',
            'In Progress',
            'Resolved'
        ]
    )->default('Open');

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
        Schema::dropIfExists('site_issues');
    }
};
