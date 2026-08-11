<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_receiveds', function (Blueprint $table) {
            if (! Schema::hasColumn('material_receiveds', 'vendor_id')) {
                $table->foreignId('vendor_id')
                    ->nullable()
                    ->after('unit')
                    ->constrained('vendors')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('material_receiveds', 'submitted_at')) {
                $table->timestamp('submitted_at')
                    ->nullable()
                    ->after('status');
            }

            if (! Schema::hasColumn('material_receiveds', 'approved_at')) {
                $table->timestamp('approved_at')
                    ->nullable()
                    ->after('submitted_at');
            }

            if (! Schema::hasColumn('material_receiveds', 'approved_by')) {
                $table->foreignId('approved_by')
                    ->nullable()
                    ->after('approved_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('material_receiveds', function (Blueprint $table) {
            if (Schema::hasColumn('material_receiveds', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }

            if (Schema::hasColumn('material_receiveds', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            if (Schema::hasColumn('material_receiveds', 'submitted_at')) {
                $table->dropColumn('submitted_at');
            }

            if (Schema::hasColumn('material_receiveds', 'vendor_id')) {
                $table->dropConstrainedForeignId('vendor_id');
            }
        });
    }
};