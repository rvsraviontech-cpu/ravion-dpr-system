<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('google_map_link');
            }

            if (!Schema::hasColumn('projects', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'latitude')) {
                $table->dropColumn('latitude');
            }

            if (Schema::hasColumn('projects', 'longitude')) {
                $table->dropColumn('longitude');
            }
        });
    }
};