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
        Schema::create('dpr_labour_attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dpr_id')
                ->constrained('dprs')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('labour_attendance_id')
                ->constrained('labour_attendances')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnUpdate()
                ->nullOnDelete();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Integrity Rules
            |--------------------------------------------------------------------------
            |
            | One attendance sheet may only be linked once to the same DPR.
            |
            */

            $table->unique(
                [
                    'dpr_id',
                    'labour_attendance_id',
                ],
                'dpr_labour_attendance_unique'
            );

            /*
            |--------------------------------------------------------------------------
            | Query Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                'labour_attendance_id',
                'dpr_labour_attendance_lookup_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'dpr_labour_attendances'
        );
    }
};