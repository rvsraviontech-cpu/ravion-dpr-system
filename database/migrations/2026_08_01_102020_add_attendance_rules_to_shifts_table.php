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
        Schema::table('shifts', function (Blueprint $table): void {
            $table
                ->unsignedSmallInteger('grace_in_minutes')
                ->default(0)
                ->after('normal_hours');

            $table
                ->unsignedSmallInteger('grace_out_minutes')
                ->default(0)
                ->after('grace_in_minutes');

            $table
                ->time('ot_start_time')
                ->nullable()
                ->after('grace_out_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table): void {
            $table->dropColumn([
                'grace_in_minutes',
                'grace_out_minutes',
                'ot_start_time',
            ]);
        });
    }
};