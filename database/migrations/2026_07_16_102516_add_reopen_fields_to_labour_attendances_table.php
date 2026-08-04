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
        Schema::table(
            'labour_attendances',
            function (Blueprint $table): void {
                $table->foreignId('reopened_by')
                    ->nullable()
                    ->after('rejected_at')
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('reopened_at')
                    ->nullable()
                    ->after('reopened_by');

                $table->text('reopen_reason')
                    ->nullable()
                    ->after('reopened_at');

                $table->unsignedInteger('revision_number')
                    ->default(0)
                    ->after('reopen_reason');

                $table->index(
                    [
                        'status',
                        'reopened_at',
                    ],
                    'la_status_reopened_idx'
                );

                $table->index(
                    'revision_number',
                    'la_revision_number_idx'
                );
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(
            'labour_attendances',
            function (Blueprint $table): void {
                $table->dropIndex(
                    'la_status_reopened_idx'
                );

                $table->dropIndex(
                    'la_revision_number_idx'
                );

                $table->dropForeign([
                    'reopened_by',
                ]);

                $table->dropColumn([
                    'reopened_by',
                    'reopened_at',
                    'reopen_reason',
                    'revision_number',
                ]);
            }
        );
    }
};