<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('work_done_item_labours', function (Blueprint $table) {
            $table->string('labour_source', 20)->nullable()->after('quantity');
            $table->foreignId('labour_attendance_detail_id')->nullable()->after('labour_source')->constrained('labour_attendance_details')->restrictOnDelete();
            $table->foreignId('labour_group_id')->nullable()->after('labour_attendance_detail_id')->constrained('labour_groups')->restrictOnDelete();
            $table->foreignId('contractor_id')->nullable()->after('labour_group_id')->constrained('contractors')->restrictOnDelete();
            $table->decimal('allocated_hours', 6, 2)->nullable()->after('contractor_id');
            $table->index(['labour_attendance_detail_id', 'labour_source'], 'wdil_attendance_source_idx');
        });
    }

    public function down(): void
    {
        Schema::table('work_done_item_labours', function (Blueprint $table) {
            $table->dropIndex('wdil_attendance_source_idx');
            $table->dropConstrainedForeignId('labour_attendance_detail_id');
            $table->dropConstrainedForeignId('labour_group_id');
            $table->dropConstrainedForeignId('contractor_id');
            $table->dropColumn(['labour_source', 'allocated_hours']);
        });
    }
};
