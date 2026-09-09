<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_requirement_items', function (Blueprint $table) {
            $table->string('specification_text', 500)
                ->nullable()
                ->after('material_specification_id');
        });
    }

    public function down(): void
    {
        Schema::table('material_requirement_items', function (Blueprint $table) {
            $table->dropColumn('specification_text');
        });
    }
};
