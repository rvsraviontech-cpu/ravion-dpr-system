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
    Schema::create('brand_masters', function (Blueprint $table) {
        $table->id();
        $table->string('brand_name');
        $table->string('brand_code')->nullable();
        $table->boolean('is_active')->default(true);
        $table->text('remarks')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('brand_masters');
}
};
