<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_floors', function (Blueprint $table) {
            if (!Schema::hasColumn('project_floors', 'usage_type')) {
                $table->string('usage_type')->default('Residential Flats')->after('sequence');
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_floors', function (Blueprint $table) {
            if (Schema::hasColumn('project_floors', 'usage_type')) {
                $table->dropColumn('usage_type');
            }
        });
    }
};