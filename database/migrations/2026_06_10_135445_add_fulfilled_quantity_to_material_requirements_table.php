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
    Schema::table('material_requirements', function (Blueprint $table) {
        $table->decimal('fulfilled_quantity', 15, 2)
              ->default(0)
              ->after('required_quantity');
    });
}

public function down(): void
{
    Schema::table('material_requirements', function (Blueprint $table) {
        $table->dropColumn('fulfilled_quantity');
    });
}
};
