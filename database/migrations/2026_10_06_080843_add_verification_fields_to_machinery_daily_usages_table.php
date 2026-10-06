<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('machinery_daily_usages', function (Blueprint $table) {
            $table->foreignId('submitted_by')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('submitted_at')
                ->nullable()
                ->after('submitted_by');

            $table->foreignId('verified_by')
                ->nullable()
                ->after('submitted_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')
                ->nullable()
                ->after('verified_by');

            $table->text('verification_remarks')
                ->nullable()
                ->after('verified_at');

            $table->foreignId('cancelled_by')
                ->nullable()
                ->after('verification_remarks')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('cancelled_at')
                ->nullable()
                ->after('cancelled_by');

            $table->text('cancellation_reason')
                ->nullable()
                ->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('machinery_daily_usages', function (Blueprint $table) {
            $table->dropForeign(['submitted_by']);
            $table->dropForeign(['verified_by']);
            $table->dropForeign(['cancelled_by']);

            $table->dropColumn([
                'submitted_by',
                'submitted_at',
                'verified_by',
                'verified_at',
                'verification_remarks',
                'cancelled_by',
                'cancelled_at',
                'cancellation_reason',
            ]);
        });
    }
};