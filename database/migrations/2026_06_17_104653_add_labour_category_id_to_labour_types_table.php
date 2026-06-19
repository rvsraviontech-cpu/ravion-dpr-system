<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('labour_types', function (Blueprint $table) {

            $table->foreignId('labour_category_id')
                ->nullable()
                ->after('id')
                ->constrained('labour_categories')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('labour_types', function (Blueprint $table) {

            $table->dropConstrainedForeignId('labour_category_id');

        });
    }
};
