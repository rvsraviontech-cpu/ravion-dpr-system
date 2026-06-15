<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {

            $table->string('name')->unique()->after('id');

            $table->string('module')->nullable()->after('name');

            $table->text('description')->nullable()->after('module');

            $table->boolean('is_active')
                  ->default(true)
                  ->after('description');

        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {

            $table->dropColumn([
                'name',
                'module',
                'description',
                'is_active'
            ]);

        });
    }
};