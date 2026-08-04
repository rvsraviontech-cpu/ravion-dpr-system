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
        Schema::create('labour_attendances', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Attendance Identification
            |--------------------------------------------------------------------------
            */

            $table->string('attendance_number', 40)
                ->unique();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->restrictOnDelete();

            $table->date('attendance_date');

            $table->foreignId('shift_id')
                ->nullable()
                ->constrained('shifts')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Attendance Summary
            |--------------------------------------------------------------------------
            |
            | These values will be recalculated from the detail rows.
            |
            */

            $table->unsignedInteger('total_labours')
                ->default(0);

            $table->unsignedInteger('present_count')
                ->default(0);

            $table->unsignedInteger('absent_count')
                ->default(0);

            $table->unsignedInteger('leave_count')
                ->default(0);

            $table->unsignedInteger('half_day_count')
                ->default(0);

            $table->decimal('total_normal_hours', 12, 2)
                ->default(0);

            $table->decimal('total_ot_hours', 12, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Workflow
            |--------------------------------------------------------------------------
            */

            $table->string('status', 30)
                ->default('draft');

            $table->foreignId('recorded_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('submitted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('submitted_at')
                ->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')
                ->nullable();

            $table->foreignId('rejected_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('rejected_at')
                ->nullable();

            $table->text('rejection_reason')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | General Information
            |--------------------------------------------------------------------------
            */

            $table->text('remarks')
                ->nullable();

            $table->boolean('is_active')
                ->default(true);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Constraints and Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique(
                [
                    'project_id',
                    'attendance_date',
                    'shift_id',
                ],
                'labour_attendance_project_date_shift_unique'
            );

            $table->index([
                'attendance_date',
                'status',
            ]);

            $table->index([
                'project_id',
                'status',
            ]);

            $table->index('recorded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labour_attendances');
    }
};