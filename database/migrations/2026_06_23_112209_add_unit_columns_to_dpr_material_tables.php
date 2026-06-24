<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dpr_materials', function (Blueprint $table) {
            if (!Schema::hasColumn('dpr_materials', 'unit')) {
                $table->string('unit')->nullable()->after('quantity_used');
            }
        });

        Schema::table('dpr_material_receiveds', function (Blueprint $table) {
            if (!Schema::hasColumn('dpr_material_receiveds', 'unit')) {
                $table->string('unit')->nullable()->after('quantity_received');
            }
        });

        Schema::table('dpr_material_requireds', function (Blueprint $table) {
            if (!Schema::hasColumn('dpr_material_requireds', 'unit')) {
                $table->string('unit')->nullable()->after('required_quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dpr_materials', function (Blueprint $table) {
            if (Schema::hasColumn('dpr_materials', 'unit')) {
                $table->dropColumn('unit');
            }
        });

        Schema::table('dpr_material_receiveds', function (Blueprint $table) {
            if (Schema::hasColumn('dpr_material_receiveds', 'unit')) {
                $table->dropColumn('unit');
            }
        });

        Schema::table('dpr_material_requireds', function (Blueprint $table) {
            if (Schema::hasColumn('dpr_material_requireds', 'unit')) {
                $table->dropColumn('unit');
            }
        });
    }
};