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
        Schema::create('dpr_manual_labours', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dpr_id')
                ->constrained('dprs')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('labour_id')
                ->constrained('labours')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('attendance_status_id')
                ->constrained('attendance_statuses')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('shift_id')
                ->nullable()
                ->constrained('shifts')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->decimal('normal_hours', 8, 2)
                ->default(0);

            $table->decimal('ot_hours', 8, 2)
                ->default(0);

            $table->string('reason', 100);

            $table->text('remarks')
                ->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Integrity Rules
            |--------------------------------------------------------------------------
            |
            | A labourer may only be entered once for the same DPR and shift.
            | Null shifts are additionally protected by application validation.
            |
            */

            $table->unique(
                [
                    'dpr_id',
                    'labour_id',
                    'shift_id',
                ],
                'dpr_manual_labour_shift_unique'
            );

            /*
            |--------------------------------------------------------------------------
            | Query Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                [
                    'dpr_id',
                    'attendance_status_id',
                ],
                'dpr_manual_labour_status_index'
            );

            $table->index(
                [
                    'labour_id',
                    'deleted_at',
                ],
                'dpr_manual_labour_lookup_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dpr_manual_labours');
    }
};