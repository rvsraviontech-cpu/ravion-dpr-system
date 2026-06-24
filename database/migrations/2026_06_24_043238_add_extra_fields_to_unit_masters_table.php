<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unit_masters', function (Blueprint $table) {
            if (!Schema::hasColumn('unit_masters', 'symbol')) {
                $table->string('symbol')->nullable()->after('unit_code');
            }

            if (!Schema::hasColumn('unit_masters', 'unit_type')) {
                $table->string('unit_type')->nullable()->after('symbol');
            }

            if (!Schema::hasColumn('unit_masters', 'decimal_allowed')) {
                $table->boolean('decimal_allowed')->default(true)->after('unit_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('unit_masters', function (Blueprint $table) {
            if (Schema::hasColumn('unit_masters', 'symbol')) {
                $table->dropColumn('symbol');
            }

            if (Schema::hasColumn('unit_masters', 'unit_type')) {
                $table->dropColumn('unit_type');
            }

            if (Schema::hasColumn('unit_masters', 'decimal_allowed')) {
                $table->dropColumn('decimal_allowed');
            }
        });
    }
};