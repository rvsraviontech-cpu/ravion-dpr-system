<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_receiveds', function (Blueprint $table) {
            if (! Schema::hasColumn('material_receiveds', 'accountant_verified_by')) {
                $table->foreignId('accountant_verified_by')
                    ->nullable()
                    ->after('accountant_verification_status')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('material_receiveds', 'accountant_verified_at')) {
                $table->timestamp('accountant_verified_at')
                    ->nullable()
                    ->after('accountant_verified_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('material_receiveds', function (Blueprint $table) {
            if (Schema::hasColumn('material_receiveds', 'accountant_verified_at')) {
                $table->dropColumn('accountant_verified_at');
            }

            if (Schema::hasColumn('material_receiveds', 'accountant_verified_by')) {
                $table->dropConstrainedForeignId('accountant_verified_by');
            }
        });
    }
};