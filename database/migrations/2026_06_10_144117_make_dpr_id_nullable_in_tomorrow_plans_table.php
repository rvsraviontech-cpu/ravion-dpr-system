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
    Schema::table('tomorrow_plans', function (Blueprint $table) {
        $table->foreignId('dpr_id')->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('tomorrow_plans', function (Blueprint $table) {
        $table->foreignId('dpr_id')->nullable(false)->change();
    });
}
};
