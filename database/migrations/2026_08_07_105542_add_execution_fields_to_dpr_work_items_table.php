<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dpr_work_items', function (Blueprint $table) {

            if (! Schema::hasColumn('dpr_work_items', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('dpr_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('dpr_work_items', 'work_date')) {
                $table->date('work_date')
                    ->nullable()
                    ->after('user_id');
            }

            if (! Schema::hasColumn('dpr_work_items', 'status')) {
                $table->string('status', 30)
                    ->default('Draft')
                    ->after('remarks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dpr_work_items', function (Blueprint $table) {

            if (Schema::hasColumn('dpr_work_items', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('dpr_work_items', 'work_date')) {
                $table->dropColumn('work_date');
            }

            if (Schema::hasColumn('dpr_work_items', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};