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
    Schema::create('labour_categories', function (Blueprint $table) {
        $table->id();
        $table->string('category_name');
        $table->boolean('is_active')->default(true);
        $table->text('remarks')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('labour_categories');
}
};
