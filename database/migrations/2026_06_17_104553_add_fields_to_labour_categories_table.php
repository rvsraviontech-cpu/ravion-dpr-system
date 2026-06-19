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
    Schema::table('labour_categories', function (Blueprint $table) {
        $table->string('category_name')->after('id');
        $table->boolean('is_active')->default(true)->after('category_name');
        $table->text('remarks')->nullable()->after('is_active');
    });
}

public function down(): void
{
    Schema::table('labour_categories', function (Blueprint $table) {
        $table->dropColumn([
            'category_name',
            'is_active',
            'remarks',
        ]);
    });
}
};
