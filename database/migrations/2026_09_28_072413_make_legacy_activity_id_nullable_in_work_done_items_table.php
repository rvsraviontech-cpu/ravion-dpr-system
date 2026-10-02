<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_done_items', function (Blueprint $table) {
            $table->unsignedBigInteger('activity_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        // Do not restore NOT NULL automatically.
        //
        // Once canonical Work Activities are saved, legitimate
        // records may have activity_id = NULL.
        //
        // Restoring NOT NULL would fail or require modifying
        // those records.
    }
};