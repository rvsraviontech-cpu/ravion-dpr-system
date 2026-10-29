<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_done_items', function (Blueprint $table): void {
            $table->unsignedBigInteger('bible_operation_id')
                ->nullable()
                ->after('work_activity_id');

            $table->foreign('bible_operation_id', 'wd_items_bible_op_fk')
                ->references('id')
                ->on('work_done_bible_operations')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('work_done_items', function (Blueprint $table): void {
            $table->dropForeign('wd_items_bible_op_fk');
            $table->dropColumn('bible_operation_id');
        });
    }
};
