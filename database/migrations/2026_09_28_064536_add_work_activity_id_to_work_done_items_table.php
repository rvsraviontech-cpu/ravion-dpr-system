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
        Schema::table('work_done_items', function (Blueprint $table) {
            $table->foreignId('work_activity_id')
                ->nullable()
                ->after('activity_mapping_id')
                ->constrained('work_activities')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_done_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('work_activity_id');
        });
    }
};